<?php
/**
 * Baseline security controls for the VIVE site.
 *
 * These controls reduce the public attack surface. They are deliberately kept
 * independent from plugins so they remain active after a migration/restore.
 * Server credentials, WordPress salts, updates and malware cleanup still need
 * to be handled outside the theme.
 *
 * @package Theme_Vive
 */

defined( 'ABSPATH' ) || exit;

// Prevent administrators from editing executable theme/plugin files in wp-admin.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Disable legacy remote publishing unless wp-config.php explicitly opts in.
if ( ! defined( 'THEME_VIVE_ALLOW_XMLRPC' ) || ! THEME_VIVE_ALLOW_XMLRPC ) {
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'wp_headers', static function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	} );
	add_filter( 'bloginfo_url', static function ( $output, $show ) {
		return 'pingback_url' === $show ? '' : $output;
	}, 10, 2 );
}

// Application passwords are unnecessary for this site and are a persistent
// access path if an administrator account is ever compromised.
if ( ! defined( 'THEME_VIVE_ALLOW_APPLICATION_PASSWORDS' ) || ! THEME_VIVE_ALLOW_APPLICATION_PASSWORDS ) {
	add_filter( 'wp_is_application_passwords_available', '__return_false' );
}

// Do not expose the installed WordPress version in generated markup or feeds.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Avoid confirming whether a supplied username or email address exists.
add_filter( 'login_errors', static function () {
	return __( 'Login failed. Please verify your credentials and try again.', 'theme-vive' );
} );

// Public author archives and REST user collections are common username
// enumeration paths. Logged-in editors keep normal REST access.
add_action( 'template_redirect', static function () {
	if ( ! is_admin() && is_author() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, -1000 );

add_filter( 'rest_pre_dispatch', static function ( $result, $server, $request ) {
	$route = $request->get_route();
	if ( ! is_user_logged_in() && preg_match( '#^/wp/v2/users(?:/|$)#', $route ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Authentication is required.', 'theme-vive' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}
	return $result;
}, 10, 3 );

// Browser-side defense in depth. CSP is intentionally omitted because this
// mirrored storefront loads assets from multiple external origins.
add_filter( 'wp_headers', static function ( $headers ) {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['Permissions-Policy']     = 'camera=(), microphone=(), geolocation=()';
	return $headers;
} );

/**
 * Block PHP execution below wp-content/uploads on Apache/LiteSpeed.
 *
 * @return void
 */
function theme_vive_protect_uploads_directory() {
	$uploads = wp_get_upload_dir();
	if ( ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
		return;
	}

	$rule_file = trailingslashit( $uploads['basedir'] ) . '.htaccess';
	$rules     = "# VIVE security: uploaded files must never execute as PHP.\n"
		. "<FilesMatch \"\\.(?:php[0-9]?|phtml|phar)$\">\n"
		. "    <IfModule mod_authz_core.c>\n"
		. "        Require all denied\n"
		. "    </IfModule>\n"
		. "    <IfModule !mod_authz_core.c>\n"
		. "        Deny from all\n"
		. "    </IfModule>\n"
		. "</FilesMatch>\n";

	if ( is_file( $rule_file ) && false !== strpos( (string) file_get_contents( $rule_file ), '# VIVE security:' ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		return;
	}

	// Never overwrite hosting or plugin rules that may already exist.
	$existing = is_file( $rule_file ) ? (string) file_get_contents( $rule_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$result   = file_put_contents( $rule_file, $rules . $existing, LOCK_EX ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( false === $result && is_admin() ) {
		update_option( 'theme_vive_uploads_protection_failed', 1, false );
	} else {
		delete_option( 'theme_vive_uploads_protection_failed' );
	}
}
add_action( 'after_switch_theme', 'theme_vive_protect_uploads_directory' );

add_action( 'admin_init', static function () {
	// Re-apply after migrations that copy the database but omit dotfiles.
	if ( current_user_can( 'manage_options' ) ) {
		theme_vive_protect_uploads_directory();
	}
} );

add_action( 'admin_notices', static function () {
	if ( current_user_can( 'manage_options' ) && get_option( 'theme_vive_uploads_protection_failed' ) ) {
		echo '<div class="notice notice-error"><p>'
			. esc_html__( 'Security warning: WordPress could not protect wp-content/uploads from PHP execution. Configure this rule at the web-server level.', 'theme-vive' )
			. '</p></div>';
	}
} );
