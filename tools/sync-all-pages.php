<?php
/**
 * Synchronize and import all site pages into WordPress database (post_type = 'page').
 */
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$theme_root = dirname( __DIR__ );
$pages_root = $theme_root . '/pages/us';

echo "=========================================================\n";
echo "1. Scanning all pages in theme (pages/us and footer-sites)\n";
echo "=========================================================\n";

function theme_vive_get_clean_title( $file, $route ) {
	$handle = fopen( $file, 'rb' );
	$head   = $handle ? fread( $handle, 65536 ) : '';
	if ( $handle ) {
		fclose( $handle );
	}
	$title = '';
	if ( preg_match( '#<title\b[^>]*>(.*?)</title>#is', (string) $head, $match ) ) {
		$title = trim( wp_strip_all_tags( html_entity_decode( $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		$title = preg_replace( '#\s*[|\-–—]\s*(?:HTC\s+)?VIVE.*$#iu', '', $title );
	}
	if ( '' === $title ) {
		if ( '/' === $route ) {
			return 'HTC VIVE - Kính thực tế ảo VR, AR, MR và trải nghiệm nhập vai';
		}
		$parts = explode( '/', trim( $route, '/' ) );
		$title = ucwords( str_replace( array( '-', '_' ), ' ', end( $parts ) ) );
	}
	if ( function_exists( 'theme_vive_import_vietnamese_title' ) ) {
		$title = theme_vive_import_vietnamese_title( $title );
	}
	return $title;
}

$scanned_routes = array();

// 1. Scan pages/us
$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $pages_root, FilesystemIterator::SKIP_DOTS )
);
foreach ( $it as $entry ) {
	if ( ! $entry->isFile() || ! preg_match( '#^index\.html?$#i', $entry->getFilename() ) ) {
		continue;
	}
	$file  = $entry->getPathname();
	$rel   = str_replace( '\\', '/', substr( $file, strlen( $pages_root ) + 1 ) );
	$rel   = preg_replace( '#(?:^|/)index\.html?$#i', '', $rel );
	$route = '/' . trim( $rel, '/' );
	$route = ( '/' === $route ) ? '/' : $route . '/';

	if ( preg_match( '#template[\s_-]*mau[\s_-]*64|templatemau64#i', $route . ' ' . $file ) ) {
		continue;
	}

	$scanned_routes[ $route ] = array(
		'file'     => $file,
		'rel_file' => str_replace( '\\', '/', substr( $file, strlen( $theme_root ) + 1 ) ),
		'title'    => theme_vive_get_clean_title( $file, $route ),
		'source'   => 'pages/us',
	);
}

// 2. Scan footer-sites
$footer_map = array(
	'business.vive.com'   => '/business/',
	'developer.vive.com'  => '/developer/',
	'sync.vive.com'       => '/sync/',
	'enterprise.vive.com' => '/enterprise/',
	'locator.vive.com'    => '/locator/',
	'roundtable.vive.com' => '/roundtable/',
	'arts.vive.com'       => '/arts/',
	'vr4impact.com'       => '/vr4impact/',
	'vivex.vive.com'      => '/vivex/',
	'hub.vive.com'        => '/hub/',
);

foreach ( $footer_map as $host => $prefix ) {
	$h_dir = $theme_root . '/footer-sites/' . $host;
	if ( ! is_dir( $h_dir ) ) {
		continue;
	}
	$fit = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $h_dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $fit as $entry ) {
		if ( ! $entry->isFile() || ! preg_match( '#^index\.html?$#i', $entry->getFilename() ) ) {
			continue;
		}
		$file  = $entry->getPathname();
		$rel   = str_replace( '\\', '/', substr( $file, strlen( $h_dir ) + 1 ) );
		$rel   = preg_replace( '#(?:^|/)index\.html?$#i', '', $rel );
		$sub   = ( '' === $rel ) ? '' : trim( $rel, '/' ) . '/';
		$route = $prefix . $sub;

		if ( ! isset( $scanned_routes[ $route ] ) ) {
			$scanned_routes[ $route ] = array(
				'file'     => $file,
				'rel_file' => str_replace( '\\', '/', substr( $file, strlen( $theme_root ) + 1 ) ),
				'title'    => theme_vive_get_clean_title( $file, $route ),
				'source'   => 'footer-sites/' . $host,
			);
		}
	}
}

// Sort routes by depth so parents are processed before children
uksort( $scanned_routes, static function ( $a, $b ) {
	$da = substr_count( $a, '/' );
	$db = substr_count( $b, '/' );
	return ( $da !== $db ) ? ( $da <=> $db ) : strnatcasecmp( $a, $b );
} );

echo 'Total scanned valid routes: ' . count( $scanned_routes ) . "\n\n";

echo "=========================================================\n";
echo "2. Existing Pages in WordPress DB\n";
echo "=========================================================\n";

$existing_pages = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'any',
	'posts_per_page' => -1,
) );

$db_by_route = array();
$db_by_id    = array();
foreach ( $existing_pages as $p ) {
	$route = get_post_meta( $p->ID, '_theme_vive_mirror_route', true );
	$db_by_id[ $p->ID ] = $p;
	if ( $route ) {
		$db_by_route[ $route ] = $p->ID;
	}
}

echo 'Existing Pages in DB: ' . count( $existing_pages ) . "\n";
echo 'Existing routes matched: ' . count( $db_by_route ) . "\n\n";

$wc_cart_id     = (int) get_option( 'woocommerce_cart_page_id' );
$wc_checkout_id = (int) get_option( 'woocommerce_checkout_page_id' );
$wc_myacct_id   = (int) get_option( 'woocommerce_myaccount_page_id' );
$wc_shop_id     = (int) get_option( 'woocommerce_shop_page_id' );

echo "=========================================================\n";
echo "3. Creating / Updating Pages in DB\n";
echo "=========================================================\n";

$created_by_route = array();
// Pre-populate with existing route mappings
foreach ( $db_by_route as $r => $id ) {
	$created_by_route[ $r ] = $id;
}

$created_count = 0;
$updated_count = 0;

foreach ( $scanned_routes as $route => $info ) {
	// Find parent route
	$parent_route = trailingslashit( dirname( untrailingslashit( $route ) ) );
	if ( './' === $parent_route || '//' === $parent_route ) {
		$parent_route = '/';
	}
	$parent_id = 0;
	while ( '/' !== $parent_route ) {
		if ( isset( $created_by_route[ $parent_route ] ) ) {
			$parent_id = $created_by_route[ $parent_route ];
			break;
		}
		$parent_route = trailingslashit( dirname( untrailingslashit( $parent_route ) ) );
	}

	$segments = array_values( array_filter( explode( '/', trim( $route, '/' ) ) ) );
	$slug     = $segments ? sanitize_title( end( $segments ) ) : 'trang-chu';

	if ( isset( $db_by_route[ $route ] ) ) {
		// Update existing page
		$page_id = $db_by_route[ $route ];
		wp_update_post( array(
			'ID'          => $page_id,
			'post_title'  => $info['title'],
			'post_parent' => $parent_id,
			'post_status' => 'publish',
		) );
		update_post_meta( $page_id, '_theme_vive_mirror_file', $info['rel_file'] );
		$created_by_route[ $route ] = $page_id;
		++$updated_count;
	} else {
		// Create new page
		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $info['title'],
			'post_name'    => $slug,
			'post_parent'  => $parent_id,
			'post_content' => '',
			'meta_input'   => array(
				'_theme_vive_mirror_route' => $route,
				'_theme_vive_mirror_file'  => $info['rel_file'],
			),
		), true );

		if ( ! is_wp_error( $page_id ) ) {
			$created_by_route[ $route ] = $page_id;
			$db_by_route[ $route ]      = $page_id;
			++$created_count;
		} else {
			echo "Failed to create page for {$route}: " . $page_id->get_error_message() . "\n";
		}
	}
}

// Ensure Front page is set
if ( isset( $created_by_route['/'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $created_by_route['/'] );
}

echo "Created new pages: {$created_count}\n";
echo "Updated existing pages: {$updated_count}\n";
echo 'Total managed pages now in DB: ' . count( $created_by_route ) . "\n\n";

echo "=========================================================\n";
echo "4. Marking WooCommerce Private Pages as Noindex in Yoast\n";
echo "=========================================================\n";

$private_pages = array_filter( array( $wc_cart_id, $wc_checkout_id, $wc_myacct_id ) );
foreach ( $private_pages as $pid ) {
	update_post_meta( $pid, '_yoast_wpseo_meta-robots-noindex', '1' );
	update_post_meta( $pid, '_yoast_wpseo_meta-robots-nofollow', '1' );
	update_post_meta( $pid, '_theme_vive_sitemap_exclude', '1' );
	$title = get_the_title( $pid );
	echo "Marked private page [ID: {$pid}] '{$title}' -> noindex, nofollow, excluded from sitemap.\n";
}

wp_cache_flush();
echo "\nSync and update completed successfully!\n";
