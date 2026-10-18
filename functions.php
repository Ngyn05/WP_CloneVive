<?php
/** VIVE Complete Mirror theme. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( file_exists( get_template_directory() . '/inc/security-hardening.php' ) ) {
	require_once get_template_directory() . '/inc/security-hardening.php';
}
require_once get_template_directory() . '/inc/translation-cms.php';
require_once get_template_directory() . '/inc/blog-cms.php';
require_once get_template_directory() . '/inc/rest-api.php';
require_once get_template_directory() . '/inc/product-cms.php';

/** reCAPTCHA v2 keys are defined in wp-config.php, outside the theme. */
function theme_vive_recaptcha_site_key() {
	return defined( 'THEME_VIVE_RECAPTCHA_SITE_KEY' ) ? trim( (string) THEME_VIVE_RECAPTCHA_SITE_KEY ) : '';
}

function theme_vive_recaptcha_secret_key() {
	return defined( 'THEME_VIVE_RECAPTCHA_SECRET_KEY' ) ? trim( (string) THEME_VIVE_RECAPTCHA_SECRET_KEY ) : '';
}

function theme_vive_recaptcha_enabled() {
	return '' !== theme_vive_recaptcha_site_key() && '' !== theme_vive_recaptcha_secret_key();
}

function theme_vive_recaptcha_field() {
	if ( ! theme_vive_recaptcha_enabled() ) { return ''; }
	return '<div class="theme-vive-recaptcha"><div class="g-recaptcha" data-sitekey="' . esc_attr( theme_vive_recaptcha_site_key() ) . '"></div></div>';
}

function theme_vive_verify_recaptcha() {
	if ( ! theme_vive_recaptcha_enabled() ) {
		return new WP_Error( 'recaptcha_not_configured', 'CAPTCHA chưa được cấu hình.' );
	}
	$token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) );
	if ( '' === $token ) {
		return new WP_Error( 'recaptcha_missing', 'Vui lòng xác nhận bạn không phải là người máy.' );
	}
	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
		'timeout' => 10,
		'body'    => array(
			'secret'   => theme_vive_recaptcha_secret_key(),
			'response' => $token,
			'remoteip' => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
		),
	) );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'recaptcha_unavailable', 'Không thể kiểm tra CAPTCHA. Vui lòng thử lại.' );
	}
	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $result['success'] ) ) {
		return new WP_Error( 'recaptcha_invalid', 'CAPTCHA không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.' );
	}
	return true;
}

add_action( 'wp_enqueue_scripts', static function () {
	if ( theme_vive_recaptcha_enabled() ) {
		wp_enqueue_script( 'google-recaptcha-v2', 'https://www.google.com/recaptcha/api.js?hl=vi', array(), null, true );
	}
}, 20 );

function theme_vive_is_blog_landing_request() {
	if ( is_admin() ) { return false; }
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return '/blog' === untrailingslashit( (string) $path );
}

function theme_vive_is_contact_request() {
	if ( is_admin() ) { return false; }
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return '/contact' === untrailingslashit( (string) $path );
}

// Standardize document titles across all public pages, virtual routes, and Yoast outputs
add_filter( 'wpseo_title', static function ( $title ) {
	global $theme_vive_virtual_seo;
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = untrailingslashit( (string) $path );
	if ( '' === $path || '/' === $path ) {
		return 'VIVE Việt Nam | Kính thực tế ảo VR & Giải pháp XR chính hãng';
	}
	if ( ! empty( $theme_vive_virtual_seo['title'] ) ) {
		$v_title = $theme_vive_virtual_seo['title'];
		$v_title = preg_replace( '/\s*[-|–—]\s*(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE|Cửa\s*hàng\s*VIVE|VIVE\s*Store|Hoa\s*Kỳ|Việt\s*Nam).*$/iu', '', (string) $v_title );
		$v_title = preg_replace( '/^(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE)\s*[-|–—]\s*/iu', '', (string) $v_title );
		$v_title = trim( $v_title );
		return $v_title ? $v_title . ' | VIVE Việt Nam' : 'VIVE Việt Nam';
	}
	if ( theme_vive_is_contact_request() ) { return 'Liên hệ & Hệ thống Văn phòng | VIVE Việt Nam'; }
	if ( theme_vive_is_blog_landing_request() ) { return 'Tin tức VR, XR và công nghệ VIVE | VIVE Việt Nam'; }
	$clean_title = preg_replace( '/\s*[-|–—]\s*(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE|Cửa\s*hàng\s*VIVE|VIVE\s*Store|Hoa\s*Kỳ|Việt\s*Nam).*$/iu', '', (string) $title );
	$clean_title = preg_replace( '/^(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE)\s*[-|–—]\s*/iu', '', (string) $clean_title );
	$clean_title = trim( $clean_title );
	return $clean_title ? $clean_title . ' | VIVE Việt Nam' : $title;
}, 99 );
add_filter( 'pre_get_document_title', static function ( $title ) {
	$path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = untrailingslashit( (string) $path );
	if ( '' === $path || '/' === $path ) {
		return 'VIVE Việt Nam | Kính thực tế ảo VR & Giải pháp XR chính hãng';
	}
	return $title;
}, 99 );
add_filter( 'wpseo_metadesc', static function ( $description ) {
	global $theme_vive_virtual_seo;
	if ( ! empty( $theme_vive_virtual_seo['description'] ) ) { return $theme_vive_virtual_seo['description']; }
	if ( theme_vive_is_contact_request() ) { return 'Hệ thống văn phòng và trung tâm trải nghiệm kính thực tế ảo VIVE tại Hà Nội và TP. Hồ Chí Minh. Nhận tư vấn chuyên sâu về giải pháp XR.'; }
	return theme_vive_is_blog_landing_request() ? 'Cập nhật tin tức mới nhất về thực tế ảo, XR, VIVERSE, thiết bị VIVE và các ứng dụng công nghệ nhập vai tại VIVE Việt Nam.' : $description;
}, 5 );
add_filter( 'wpseo_canonical', static function ( $canonical ) {
	global $theme_vive_virtual_seo;
	if ( ! empty( $theme_vive_virtual_seo['url'] ) ) { return $theme_vive_virtual_seo['url']; }
	if ( theme_vive_is_contact_request() ) { return home_url( '/contact/' ); }
	return theme_vive_is_blog_landing_request() ? home_url( '/blog/' ) : $canonical;
} );
add_filter( 'wpseo_opengraph_url', static function ( $url ) {
	global $theme_vive_virtual_seo;
	if ( ! empty( $theme_vive_virtual_seo['url'] ) ) { return $theme_vive_virtual_seo['url']; }
	if ( theme_vive_is_contact_request() ) { return home_url( '/contact/' ); }
	return theme_vive_is_blog_landing_request() ? home_url( '/blog/' ) : $url;
} );
add_filter( 'wpseo_opengraph_title', static function ( $title ) {
	global $theme_vive_virtual_seo;
	if ( ! empty( $theme_vive_virtual_seo['title'] ) ) { return $theme_vive_virtual_seo['title']; }
	if ( theme_vive_is_contact_request() ) { return 'Liên hệ & Hệ thống Văn phòng | VIVE Việt Nam'; }
	return theme_vive_is_blog_landing_request() ? 'Tin tức VR, XR và công nghệ VIVE | VIVE Việt Nam' : $title;
} );
add_filter( 'wpseo_opengraph_desc', static function ( $description ) {
	global $theme_vive_virtual_seo;
	if ( ! empty( $theme_vive_virtual_seo['description'] ) ) { return $theme_vive_virtual_seo['description']; }
	if ( theme_vive_is_contact_request() ) { return 'Hệ thống văn phòng và trung tâm trải nghiệm kính thực tế ảo VIVE tại Hà Nội và TP. Hồ Chí Minh. Nhận tư vấn chuyên sâu về giải pháp XR.'; }
	return theme_vive_is_blog_landing_request() ? 'Cập nhật tin tức mới nhất về thực tế ảo, XR, VIVERSE và công nghệ nhập vai.' : $description;
} );

add_action( 'init', static function () {
	// Keep a real Page record for the virtual blog landing route. The mirror
	// router still owns the public layout, while WordPress/Yoast can manage and
	// discover the canonical /blog/ URL normally.
	if ( ! get_page_by_path( 'blog', OBJECT, 'page' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Tin tức',
			'post_name'    => 'blog',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => 'Cập nhật tin tức mới nhất về thực tế ảo, XR, VIVERSE, thiết bị VIVE và công nghệ nhập vai.',
		) );
	}
	// Auto-create WordPress Page 'contact' in DB if not existing so Yoast Sitemap automatically includes it
	if ( ! get_page_by_path( 'contact' ) ) {
		wp_insert_post( array(
			'post_title'   => 'Liên hệ & Hệ thống Văn phòng',
			'post_name'    => 'contact',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => 'Hệ thống văn phòng và trung tâm trải nghiệm kính thực tế ảo VIVE tại Hà Nội và TP. Hồ Chí Minh.',
		) );
	}
} );

// Keep Yoast's sitemap aligned with the routes this mirror can actually
// render. The audit tool marks published records whose public URL is not 200.
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', static function ( $post_ids ) {
	$excluded = get_posts( array(
		'post_type'      => array( 'page', 'post', 'product' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_theme_vive_sitemap_exclude',
		'meta_value'     => '1',
	) );
	// Blog routes are now rendered by the mirror router. Some imported posts
	// still carry an old audit exclusion from when those routes returned 404;
	// do not let that stale flag remove valid published articles from Yoast.
	$excluded = array_values( array_filter( $excluded, static function ( $post_id ) {
		return ! theme_vive_blog_is_post( $post_id );
	} ) );
	$woocommerce_private_pages = array_filter( array_map( 'absint', array(
		get_option( 'woocommerce_cart_page_id' ),
		get_option( 'woocommerce_checkout_page_id' ),
		get_option( 'woocommerce_myaccount_page_id' ),
	) ) );
	return array_values( array_unique( array_merge( (array) $post_ids, $excluded, $woocommerce_private_pages ) ) );
} );

// These native archives are not rendered by the mirror router. Individual
// articles, pages and products remain discoverable in their own sitemaps.
add_filter( 'wpseo_sitemap_exclude_taxonomy', static function ( $excluded, $taxonomy ) {
	return 'category' === $taxonomy ? true : $excluded;
}, 10, 2 );
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

// Yoast indexables may retain the old root-level permalink after imported blog
// posts, mirrored pages, or WooCommerce catalog items are assigned their routes.
// Enforce canonical public URLs at sitemap generation time so crawlers receive 200 OK.
add_filter( 'wpseo_sitemap_entry', static function ( $entry, $object_type, $object ) {
	if ( 'post' === $object_type && $object instanceof WP_Post ) {
		if ( 'post' === $object->post_type && theme_vive_blog_is_post( $object->ID ) ) {
			$entry['loc'] = theme_vive_blog_url( $object );
		} elseif ( 'page' === $object->post_type ) {
			$mirror_route = get_post_meta( $object->ID, '_theme_vive_mirror_route', true );
			if ( $mirror_route ) {
				$entry['loc'] = home_url( $mirror_route );
			}
		} elseif ( 'product' === $object->post_type ) {
			$entry['loc'] = home_url( '/shop/products/' . $object->post_name . '/' );
		}
	} elseif ( 'term' === $object_type && $object instanceof WP_Term ) {
		if ( 'product_cat' === $object->taxonomy ) {
			$entry['loc'] = home_url( '/shop/collections/' . $object->slug . '/' );
		}
	}
	return $entry;
}, 10, 3 );

/**
 * Configure SEO-optimized robots.txt rules according to website structure and Yoast SEO standards.
 *
 * @param string $output The default robots.txt content.
 * @param bool   $public Whether the site is considered public.
 * @return string Filtered robots.txt content.
 */
function theme_vive_custom_robots_txt( $output, $public ) {
	if ( '0' === (string) $public ) {
		return $output;
	}

	$sitemap_url = home_url( '/sitemap_index.xml' );

	$rules = array(
		'User-agent: *',
		'# WordPress Core & Admin',
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'Disallow: /wp-login.php',
		'Disallow: /wp-register.php',
		'Disallow: /xmlrpc.php',
		'',
		'# WooCommerce User & Checkout Pages (No Index)',
		'Disallow: /cart/',
		'Disallow: /gio-hang/',
		'Disallow: /checkout/',
		'Disallow: /thanh-toan/',
		'Disallow: /my-account/',
		'Disallow: /tai-khoan/',
		'Disallow: /order-received/',
		'Disallow: /order-pay/',
		'',
		'# WooCommerce Internal Logs & Temporary Uploads',
		'Disallow: /wp-content/uploads/wc-logs/',
		'Disallow: /wp-content/uploads/woocommerce_transient_files/',
		'Disallow: /wp-content/uploads/woocommerce_uploads/',
		'',
		'# Dynamic Parameters, Feeds & Trackbacks',
		'Disallow: /*?add-to-cart=',
		'Disallow: /*?*add-to-cart=',
		'Disallow: /*?s=',
		'Disallow: /search/',
		'Disallow: /feed/',
		'Disallow: /*/feed/',
		'Disallow: /trackback/',
		'Disallow: /*/trackback/',
		'',
		'# Allow Search Crawlers full access to static assets for rendering',
		'Allow: /wp-content/uploads/',
		'Allow: /wp-content/themes/',
		'Allow: /wp-content/plugins/',
		'Allow: /wp-includes/js/',
		'Allow: /wp-includes/css/',
		'',
		'# Yoast SEO XML Sitemap Index',
		'Sitemap: ' . $sitemap_url,
	);

	return implode( "\n", $rules ) . "\n";
}
add_filter( 'robots_txt', 'theme_vive_custom_robots_txt', 999, 2 );

// Mirrored pages have no WordPress featured image. Supply Yoast with the
// original page/article image selected during rendering so Open Graph and
// Twitter cards are still complete and URL-specific.
add_filter( 'wpseo_add_opengraph_images', static function ( $images ) {
	global $theme_vive_seo_image;
	$image = $theme_vive_seo_image ?: get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg';
	if ( is_object( $images ) && method_exists( $images, 'add_image_by_url' ) ) {
		$images->add_image_by_url( $image );
	}
	return $images;
} );
add_filter( 'wpseo_twitter_image', static function ( $image ) {
	global $theme_vive_seo_image;
	return $image ?: ( $theme_vive_seo_image ?: get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg' );
} );

// WooCommerce product-category archives do not have an editor body from
// which Yoast can derive a description. Provide a concise, term-specific
// fallback while preserving any description entered manually in Yoast.
add_filter( 'wpseo_metadesc', static function ( $description ) {
	if ( ! $description && function_exists( 'is_product_category' ) && is_product_category() ) {
		$name = single_term_title( '', false );
		return sprintf( 'Khám phá %s tại VIVE Việt Nam: thông tin sản phẩm, tính năng nổi bật và lựa chọn phù hợp cho nhu cầu thực tế ảo của bạn.', $name );
	}
	return $description;
} );

add_action( 'after_setup_theme', static function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
} );

// Product records remain publicly viewable even while the WooCommerce shop
// itself is still configured in "Coming soon" mode.
add_filter( 'woocommerce_coming_soon_exclude', static function ( $excluded ) {
	return is_singular( 'product' ) ? true : $excluded;
} );

// Lightweight endpoint for an external host/container health monitor. It
// exercises WordPress and its database without rendering a mirrored page.
add_action( 'template_redirect', static function () {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
	if ( '/healthz' !== untrailingslashit( (string) $path ) ) { return; }
	global $wpdb;
	$db_ok = '1' === (string) $wpdb->get_var( 'SELECT 1' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	status_header( $db_ok ? 200 : 503 );
	header( 'Content-Type: application/json; charset=UTF-8' );
	header( 'Cache-Control: no-store, max-age=0' );
	echo wp_json_encode( array(
		'status' => $db_ok ? 'ok' : 'database_unavailable',
		'site'   => 'vive-vn',
		'time'   => gmdate( 'c' ),
	) );
	exit;
}, -999 );

// One-click cache/OPcache refresh for local development workers.
add_action( 'admin_bar_menu', static function ( $admin_bar ) {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) { return; }
	$return_url = wp_get_referer();
	if ( ! $return_url ) {
		$return_url = is_admin() ? admin_url() : home_url( '/' );
	}
	$url = wp_nonce_url(
		add_query_arg(
			array(
				'action' => 'theme_vive_refresh_worker',
				'return' => $return_url,
			),
			admin_url( 'admin-post.php' )
		),
		'theme_vive_refresh_worker'
	);
	$admin_bar->add_node( array(
		'id'    => 'theme-vive-refresh-worker',
		'title' => 'Làm mới Worker',
		'href'  => $url,
		'meta'  => array( 'title' => 'Xóa cache và làm mới PHP OPcache' ),
	) );
}, 100 );

add_action( 'admin_post_theme_vive_refresh_worker', static function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Bạn không có quyền thực hiện thao tác này.' ); }
	check_admin_referer( 'theme_vive_refresh_worker' );
	delete_transient( 'theme_vive_blog_cards_v2' );
	delete_transient( 'theme_vive_blog_cards_v3' );
	delete_transient( 'theme_vive_blog_cards_v4' );
	delete_transient( 'theme_vive_blog_cards_v5' );
	wp_cache_flush();
	$opcache_reset = false;
	if ( function_exists( 'opcache_reset' ) ) {
		$opcache_reset = (bool) @opcache_reset(); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	set_transient( 'theme_vive_worker_refresh_notice_' . get_current_user_id(), $opcache_reset ? 'Đã làm mới cache và PHP OPcache.' : 'Đã làm mới cache WordPress.', MINUTE_IN_SECONDS );
	$return_url = isset( $_GET['return'] ) ? sanitize_text_field( wp_unslash( $_GET['return'] ) ) : admin_url();
	wp_safe_redirect( wp_validate_redirect( $return_url, admin_url() ) );
	exit;
} );

add_action( 'admin_notices', static function () {
	$key = 'theme_vive_worker_refresh_notice_' . get_current_user_id();
	$message = get_transient( $key );
	if ( ! $message ) { return; }
	delete_transient( $key );
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
} );
add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'theme-vive', get_stylesheet_uri(), array(), '3.3.14' );
} );

// WordPress only prints a favicon when a Site Icon has been configured in the
// database. Use the mirrored VIVE favicon as a reliable fallback for native
// WooCommerce pages as well as mirrored pages rendered through wp_head().
add_action( 'wp_head', static function () {
	$favicon_file = get_template_directory() . '/assets/static/images/favicon.ico';
	$favicon_url  = get_template_directory_uri() . '/assets/static/images/favicon.ico';
	if ( is_file( $favicon_file ) ) {
		$favicon_url = add_query_arg( 'ver', (string) filemtime( $favicon_file ), $favicon_url );
	}
	echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '" sizes="any">' . "\n";
	echo '<link rel="shortcut icon" href="' . esc_url( $favicon_url ) . '">' . "\n";
}, 2 );

// Product reviews only need the customer's name and email address. Remove the
// website and browser-save fields from WooCommerce's review form.
add_filter( 'comment_form_default_fields', static function ( $fields ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		unset( $fields['url'], $fields['cookies'] );
	}
	return $fields;
}, 20 );

// Publish WooCommerce product reviews immediately after submission.
add_filter( 'pre_comment_approved', static function ( $approved, $commentdata ) {
	$post_id = isset( $commentdata['comment_post_ID'] ) ? absint( $commentdata['comment_post_ID'] ) : 0;
	if ( $post_id && 'product' === get_post_type( $post_id ) ) {
		return 1;
	}
	return $approved;
}, 20, 2 );

function theme_vive_safe_path( $path ) {
	$path = rawurldecode( (string) wp_parse_url( $path, PHP_URL_PATH ) );
	$path = '/' . ltrim( str_replace( '\\', '/', $path ), '/' );
	return ( false !== strpos( $path, "\0" ) || false !== strpos( $path, '..' ) ) ? false : $path;
}

function theme_vive_find_index( $base, $path ) {
	$path = theme_vive_safe_path( $path );
	if ( false === $path ) { return false; }
	$stem = rtrim( ltrim( $path, '/' ), '/' );
	$base = rtrim( str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $base ), DIRECTORY_SEPARATOR );
	$stem = str_replace( array( '/', '\\' ), DIRECTORY_SEPARATOR, $stem );
	
	$list = array();
	if ( '' === $stem ) {
		$list[] = $base . DIRECTORY_SEPARATOR . 'index.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . 'index.html';
	} elseif ( preg_match( '#\.html?$#i', $stem ) ) {
		$list[] = $base . DIRECTORY_SEPARATOR . $stem;
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . DIRECTORY_SEPARATOR . 'index.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . DIRECTORY_SEPARATOR . 'index.html';
	} else {
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . DIRECTORY_SEPARATOR . 'index.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . DIRECTORY_SEPARATOR . 'index.html';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . '.html' . DIRECTORY_SEPARATOR . 'index.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . '.html' . DIRECTORY_SEPARATOR . 'index.html';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . '.htm' . DIRECTORY_SEPARATOR . 'index.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . '.htm';
		$list[] = $base . DIRECTORY_SEPARATOR . $stem . '.html';
	}
	foreach ( $list as $file ) {
		if ( file_exists( $file ) && ! is_dir( $file ) ) { return $file; }
	}
	return false;
}

function theme_vive_clean_slug_path( $path ) {
	$path = '/' . ltrim( trim( (string) $path ), '/' );
	$path = preg_replace( '#^/us(?:/|$)#i', '/', $path );
	$path = preg_replace( '#/index\.html?$#i', '/', $path );
	$path = preg_replace( '#\.html?$#i', '', $path );
	$path = preg_replace( '#/{2,}#', '/', $path );
	$path = '/' . trim( $path, '/' );
	return '/' === $path ? '/' : $path . '/';
}

function theme_vive_customer_support_html() {
	static $cached_html = null;
	if ( null !== $cached_html ) { return $cached_html; }

	$home_url = esc_url( home_url( '/' ) );
	$hanoi_phone = '024.7305.3268';
	$hanoi_tel = '02473053268';
	$hanoi_address = '226 Đường Láng, Phường Thịnh Quang, Quận Đống Đa, Hà Nội.';

	$hcm_phone = '028.7305.3268';
	$hcm_tel = '02873053268';
	$hcm_address = '137 Đường Hòa Hưng, Phường Hòa Hưng, TP. Hồ Chí Minh.';

	$hotline = '1900.63.8400';
	$hotline_tel = '1900638400';
	$hotline_desc = 'Hỗ trợ và tư vấn khách hàng mọi lúc, mọi nơi.';

	ob_start();
	?>
	<section class="vive-support-section" aria-label="Thông tin hỗ trợ khách hàng VIVE Việt Nam">
		<div class="vive-support-container">
			<div class="vive-support-topbar">
				<div class="vive-support-brand">
					<a href="<?php echo $home_url; ?>" class="vive-support-logo-link" aria-label="VIVE Việt Nam">
						<svg class="vive-support-official-logo" viewBox="0 0 132 33" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M23.1972 1.21539C22.7626 0.462404 21.9592 0 21.09 0H16.2742C15.405 0 14.6015 0.464931 14.167 1.21792L0.325938 25.1846C-0.108646 25.9376 -0.108646 26.8649 0.325938 27.6179L2.73131 31.7821C3.1659 32.5351 3.96937 33 4.83854 33H32.5281C33.3973 33 34.2008 32.5351 34.6354 31.7846L37.0407 27.6179C37.4753 26.8649 37.4753 25.9376 37.0407 25.1821L23.1972 1.21539ZM25.2842 27.4966C23.1997 28.1536 20.9813 28.5073 18.6796 28.5073C16.3778 28.5073 14.1644 28.1536 12.0799 27.4966C9.64171 26.7285 8.13583 24.2547 8.66137 21.7532C9.61645 17.1999 11.9814 13.1672 15.2812 10.1325C17.2065 8.3637 20.1526 8.36118 22.0754 10.1325C25.3752 13.1672 27.7427 17.1999 28.6977 21.7532C29.2233 24.2547 27.7174 26.7285 25.2817 27.4966H25.2842Z" fill="#00B2E3"/>
							<path d="M66.5725 9.60584L58.2068 26.0629C58.0729 26.3257 57.8 26.495 57.4993 26.495H54.7402C54.4421 26.495 54.1717 26.3282 54.0327 26.0629L45.6695 9.60584C45.528 9.33042 45.5407 9.00194 45.6897 8.7341L46.5842 7.1877C46.756 6.88953 47.0743 6.70508 47.4205 6.70508H48.2341C48.4438 6.70508 48.6409 6.82131 48.7318 7.01335L55.761 20.9992C55.9075 21.2923 56.3269 21.2923 56.4735 20.9992L63.5001 7.01335C63.5911 6.82131 63.7882 6.70508 64.0029 6.70508H64.8114C65.1551 6.70508 65.476 6.88953 65.6478 7.1877L66.5447 8.73915C66.6989 9.00447 66.7064 9.33042 66.5675 9.60584" fill="#ffffff"/>
							<path d="M78.5535 6.94007C78.4094 6.79604 78.2073 6.70508 77.985 6.70508H76.7343C76.3881 6.70508 76.0698 6.88953 75.898 7.19022L75.006 8.73915C74.9277 8.87813 74.8848 9.03479 74.8848 9.19398V25.694C74.8848 26.1362 75.2436 26.495 75.6857 26.495H77.9951C78.4347 26.495 78.791 26.1387 78.791 25.699V7.5086C78.791 7.28624 78.7 7.08662 78.556 6.94007" fill="#ffffff"/>
							<path d="M108.002 9.60584L99.6365 26.0629C99.5025 26.3257 99.2297 26.495 98.929 26.495H96.1699C95.8717 26.495 95.6014 26.3282 95.4624 26.0629L87.0992 9.60584C86.9577 9.33042 86.9703 9.00194 87.1194 8.7341L88.0138 7.19022C88.1882 6.88953 88.5091 6.70508 88.8552 6.70508H89.6663C89.876 6.70508 90.0731 6.82131 90.164 7.01335L97.1932 20.9992C97.3397 21.2923 97.7592 21.2923 97.9057 20.9992L104.932 7.01335C105.023 6.82131 105.22 6.70508 105.435 6.70508H106.241C106.587 6.70508 106.908 6.88953 107.083 7.19022L107.977 8.73915C108.131 9.00447 108.139 9.33042 108 9.60584" fill="#ffffff"/>
							<path d="M117.33 7.1877C117.502 6.88953 117.823 6.70508 118.169 6.70508H130.148C130.486 6.70508 130.8 6.88448 130.969 7.17759L131.813 8.62797C131.932 8.83517 131.997 9.07269 131.997 9.31274V9.67407C131.997 9.9697 131.755 10.2173 131.452 10.2173H120.531C120.312 10.2173 120.132 10.3967 120.132 10.6166V14.4042C120.132 14.6241 120.312 14.8035 120.531 14.8035H127.932C128.291 14.8085 128.584 15.1016 128.584 15.4604V17.6385C128.584 17.9999 128.288 18.2955 127.927 18.2955H120.531C120.312 18.2955 120.132 18.4749 120.132 18.6947V22.586C120.132 22.8058 120.312 22.9852 120.531 22.9852H131.454C131.755 22.9852 132 23.2278 132 23.5235V23.8873C132 24.1299 131.937 24.3649 131.815 24.5721L130.972 26.025C130.802 26.3181 130.489 26.4975 130.15 26.4975H118.172C117.825 26.4975 117.507 26.313 117.333 26.0149L116.499 24.5771C116.378 24.3674 116.314 24.1299 116.314 23.8898V9.31021C116.314 9.07016 116.38 8.83517 116.499 8.62545L117.333 7.1877H117.33Z" fill="#ffffff"/>
						</svg>
					</a>
					<span class="vive-support-badge-vn">VIỆT NAM</span>
				</div>
				<div class="vive-support-tagline">
					Giải pháp kính thông minh cho doanh nghiệp Việt
				</div>
			</div>

			<h2 class="vive-support-title">HỖ TRỢ KHÁCH HÀNG</h2>

			<div class="vive-support-grid">
				<!-- Card Hà Nội -->
				<div class="vive-support-card">
					<div class="vive-support-card-head">
						<div class="vive-support-card-icon-box" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M3 21h18M5 21V7l8-4v18M13 11l6 3v7M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
							</svg>
						</div>
						<span class="vive-support-card-tag">CHI NHÁNH MIỀN BẮC</span>
					</div>
					<h3 class="vive-support-card-name">Văn Phòng Hà Nội</h3>
					<a href="tel:<?php echo esc_attr( $hanoi_tel ); ?>" class="vive-support-card-phone" aria-label="Gọi điện Văn phòng Hà Nội"><?php echo esc_html( $hanoi_phone ); ?></a>
					<p class="vive-support-card-address"><?php echo esc_html( $hanoi_address ); ?></p>
				</div>

				<!-- Card TP.HCM -->
				<div class="vive-support-card">
					<div class="vive-support-card-head">
						<div class="vive-support-card-icon-box" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M3 21h18M5 21V7l8-4v18M13 11l6 3v7M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
							</svg>
						</div>
						<span class="vive-support-card-tag">CHI NHÁNH MIỀN NAM</span>
					</div>
					<h3 class="vive-support-card-name">Văn Phòng TP.HCM</h3>
					<a href="tel:<?php echo esc_attr( $hcm_tel ); ?>" class="vive-support-card-phone" aria-label="Gọi điện Văn phòng TP.HCM"><?php echo esc_html( $hcm_phone ); ?></a>
					<p class="vive-support-card-address"><?php echo esc_html( $hcm_address ); ?></p>
				</div>

				<!-- Card Hotline -->
				<div class="vive-support-card vive-support-card--hotline">
					<div class="vive-support-card-head">
						<div class="vive-support-card-icon-box vive-support-card-icon-box--hotline" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
							</svg>
						</div>
						<span class="vive-support-card-tag vive-support-card-tag--hotline">TƯ VẤN 24/7</span>
					</div>
					<h3 class="vive-support-card-name">HOTLINE TỔNG ĐÀI</h3>
					<a href="tel:<?php echo esc_attr( $hotline_tel ); ?>" class="vive-support-card-phone vive-support-card-phone--hotline" aria-label="Gọi Tổng đài tư vấn"><?php echo esc_html( $hotline ); ?></a>
					<p class="vive-support-card-address"><?php echo esc_html( $hotline_desc ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- ==========================================================================
	     VIVE Floating Contact Button & Interactive Modals
	     ========================================================================== -->
	<div class="vive-floating-widget" id="viveFloatingWidget">
		<!-- Nút nổi gọi điện thoại -->
		<button type="button" class="vive-float-btn" id="viveFloatTrigger" aria-label="Liên hệ VIVE Việt Nam" title="Liên hệ với VIVE Việt Nam">
			<span class="vive-float-pulse"></span>
			<span class="vive-float-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
				</svg>
			</span>
		</button>

		<!-- Backdrop Overlay -->
		<div class="vive-float-backdrop" id="viveFloatBackdrop" aria-hidden="true"></div>

		<!-- Popup 1: Menu Liên hệ rút gọn -->
		<div class="vive-quick-contact" id="viveQuickContact" role="dialog" aria-modal="true" aria-labelledby="viveQuickTitle" aria-hidden="true">
			<div class="vive-quick-contact__head">
				<div class="vive-quick-contact__title" id="viveQuickTitle">Liên hệ với VIVE Việt Nam</div>
				<button type="button" class="vive-quick-contact__close" id="viveQuickClose" aria-label="Đóng">&times;</button>
			</div>
			<div class="vive-quick-contact__body">
				<!-- Hotline -->
				<a href="tel:<?php echo esc_attr( $hotline_tel ); ?>" class="vive-quick-item">
					<div class="vive-quick-item__icon vive-quick-item__icon--phone" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
						</svg>
					</div>
					<div class="vive-quick-item__content">
						<span class="vive-quick-item__label">Số điện thoại Hotline</span>
						<strong class="vive-quick-item__val vive-quick-item__val--hotline"><?php echo esc_html( $hotline ); ?></strong>
					</div>
				</a>

				<!-- Zalo -->
				<a href="https://zalo.me/0917834532" target="_blank" rel="noopener noreferrer" class="vive-quick-item">
					<div class="vive-quick-item__icon vive-quick-item__icon--zalo" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="currentColor">
							<path d="M12 2C6.48 2 2 6.03 2 11c0 2.88 1.51 5.43 3.86 7.03-.17.92-.72 2.76-.78 3.01-.1.4.3.74.67.5 1.54-.98 3.19-2.07 3.65-2.38.83.24 1.7.37 2.6.37 5.52 0 10-4.03 10-9s-4.48-9-10-9z"/>
						</svg>
					</div>
					<div class="vive-quick-item__content">
						<span class="vive-quick-item__label">Chat qua Zalo</span>
						<strong class="vive-quick-item__val vive-quick-item__val--zalo">Nhắn tin tư vấn ngay</strong>
					</div>
				</a>

				<!-- Hệ thống văn phòng -->
				<button type="button" class="vive-quick-item" id="viveOpenOffices">
					<div class="vive-quick-item__icon vive-quick-item__icon--map" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
							<circle cx="12" cy="10" r="3"/>
						</svg>
					</div>
					<div class="vive-quick-item__content">
						<span class="vive-quick-item__label">Công ty VIVE Việt Nam</span>
						<strong class="vive-quick-item__val vive-quick-item__val--offices">Xem hệ thống văn phòng</strong>
					</div>
				</button>
			</div>
		</div>

		<!-- Popup 2: Modal Chi tiết Hệ thống Văn phòng -->
		<div class="vive-offices-modal" id="viveOfficesModal" role="dialog" aria-modal="true" aria-labelledby="viveOfficesModalTitle" aria-hidden="true">
			<div class="vive-offices-modal__dialog">
				<div class="vive-offices-modal__head">
					<div>
						<span class="vive-offices-modal__tag">VIVE VIỆT NAM</span>
						<h3 class="vive-offices-modal__title" id="viveOfficesModalTitle">Hệ thống văn phòng</h3>
					</div>
					<button type="button" class="vive-offices-modal__close" id="viveOfficesClose" aria-label="Đóng">&times;</button>
				</div>
				<div class="vive-offices-modal__body">
					<div class="vive-offices-modal__grid">
						<!-- Chi nhánh Hà Nội -->
						<div class="vive-branch-card">
							<div class="vive-branch-card__header">
								<span class="vive-branch-tag vive-branch-tag--city">HÀ NỘI</span>
								<span class="vive-branch-tag vive-branch-tag--sub">Chi nhánh miền Bắc</span>
							</div>
							<h4 class="vive-branch-card__title">Công ty VIVE Việt Nam – Chi nhánh Hà Nội</h4>
							
							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">ĐỊA CHỈ</span>
									<p class="vive-branch-info__text"><?php echo esc_html( $hanoi_address ); ?></p>
									<a href="https://maps.google.com/?q=226+Đường+Láng,+Thịnh+Quang,+Đống+Đa,+Hà+Nội" target="_blank" rel="noopener noreferrer" class="vive-branch-info__link">Xem trên Google Maps &rarr;</a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">ĐIỆN THOẠI</span>
									<a href="tel:<?php echo esc_attr( $hanoi_tel ); ?>" class="vive-branch-info__phone"><?php echo esc_html( $hanoi_phone ); ?></a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">EMAIL</span>
									<a href="mailto:contact@vive.com.vn" class="vive-branch-info__mail">contact@vive.com.vn</a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">GIỜ LÀM VIỆC</span>
									<p class="vive-branch-info__text">7:00 – 21:00 · Tất cả các ngày trong tuần (GMT+7)</p>
								</div>
							</div>
						</div>

						<!-- Chi nhánh TP.HCM -->
						<div class="vive-branch-card">
							<div class="vive-branch-card__header">
								<span class="vive-branch-tag vive-branch-tag--city">TP. HỒ CHÍ MINH</span>
								<span class="vive-branch-tag vive-branch-tag--sub">Chi nhánh miền Nam</span>
							</div>
							<h4 class="vive-branch-card__title">Công ty VIVE Việt Nam – Chi nhánh Hồ Chí Minh</h4>
							
							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">ĐỊA CHỈ</span>
									<p class="vive-branch-info__text"><?php echo esc_html( $hcm_address ); ?></p>
									<a href="https://maps.google.com/?q=137+Đường+Hòa+Hưng,+Hòa+Hưng,+TP+Hồ+Chí+Minh" target="_blank" rel="noopener noreferrer" class="vive-branch-info__link">Xem trên Google Maps &rarr;</a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">ĐIỆN THOẠI</span>
									<a href="tel:<?php echo esc_attr( $hcm_tel ); ?>" class="vive-branch-info__phone"><?php echo esc_html( $hcm_phone ); ?></a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">EMAIL</span>
									<a href="mailto:contact@vive.com.vn" class="vive-branch-info__mail">contact@vive.com.vn</a>
								</div>
							</div>

							<div class="vive-branch-info">
								<div class="vive-branch-info__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
								</div>
								<div>
									<span class="vive-branch-info__label">GIỜ LÀM VIỆC</span>
									<p class="vive-branch-info__text">8:00 – 20:30 · Thứ 2 đến Thứ 7 (GMT+7)</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="vive-offices-modal__foot">
					<div class="vive-offices-foot-info">
						<strong>Cần tư vấn ngay?</strong>
						<span>Đội ngũ VIVE luôn sẵn sàng hỗ trợ bạn</span>
					</div>
					<a href="tel:<?php echo esc_attr( $hotline_tel ); ?>" class="vive-offices-foot-btn">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
							<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
						</svg>
						HOTLINE 1900.63.8400
					</a>
				</div>
			</div>
		</div>
	</div>

	<script id="theme-vive-contact-widget-js">
	(function(){
		function initViveContact() {
			var trigger = document.getElementById('viveFloatTrigger');
			var backdrop = document.getElementById('viveFloatBackdrop');
			var quickBox = document.getElementById('viveQuickContact');
			var quickClose = document.getElementById('viveQuickClose');
			var openOffices = document.getElementById('viveOpenOffices');
			var officesModal = document.getElementById('viveOfficesModal');
			var officesClose = document.getElementById('viveOfficesClose');

			if (!trigger || !quickBox) return;

			function closeAll() {
				quickBox.classList.remove('is-open');
				if (officesModal) officesModal.classList.remove('is-open');
				if (backdrop) backdrop.classList.remove('is-open');
				trigger.classList.remove('is-active');
			}

			function toggleQuick() {
				if (quickBox.classList.contains('is-open') || (officesModal && officesModal.classList.contains('is-open'))) {
					closeAll();
				} else {
					quickBox.classList.add('is-open');
					if (backdrop) backdrop.classList.add('is-open');
					trigger.classList.add('is-active');
				}
			}

			trigger.addEventListener('click', function(e){
				e.preventDefault();
				toggleQuick();
			});

			if (quickClose) {
				quickClose.addEventListener('click', function(e){
					e.preventDefault();
					closeAll();
				});
			}

			if (openOffices && officesModal) {
				openOffices.addEventListener('click', function(e){
					e.preventDefault();
					quickBox.classList.remove('is-open');
					officesModal.classList.add('is-open');
					if (backdrop) backdrop.classList.add('is-open');
				});
			}

			if (officesClose) {
				officesClose.addEventListener('click', function(e){
					e.preventDefault();
					closeAll();
				});
			}

			if (backdrop) {
				backdrop.addEventListener('click', function(e){
					e.preventDefault();
					closeAll();
				});
			}

			document.addEventListener('keydown', function(e){
				if (e.key === 'Escape' || e.keyCode === 27) {
					closeAll();
				}
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initViveContact);
		} else {
			initViveContact();
		}
	})();
	</script>
	<?php
	$cached_html = ob_get_clean();
	return $cached_html;
}

function theme_vive_page_file( $host, $path ) {
	$host = strtolower( preg_replace( '#^www\.#', '', $host ) );
	$root = get_template_directory();
	$clean = theme_vive_clean_slug_path( $path );
	if ( 'vive.com' === $host || 'www' === $host ) {
		if ( '/' === $clean ) {
			return theme_vive_find_index( $root . '/pages/us', '/' );
		}
		// 1. Tìm trong pages/us/<clean>
		$file = theme_vive_find_index( $root . '/pages/us', $clean );
		if ( ! $file && 0 !== strpos( $clean, '/vive-' ) ) {
			$file = theme_vive_find_index( $root . '/pages/us', '/vive-' . ltrim( $clean, '/' ) );
		}
		if ( ! $file ) {
			$file = theme_vive_find_index( $root . '/pages', $clean );
		}
		if ( ! $file ) {
			$file = theme_vive_find_index( $root . '/footer-sites/vive.com/us', $clean );
		}
		if ( ! $file ) {
			$file = theme_vive_find_index( $root . '/footer-sites/vive.com', $clean );
		}
		if ( ! $file ) {
			$file = theme_vive_find_index( $root . '/footer-sites/blog.vive.com/us', $clean );
		}
		return $file;
	}
	if ( 'shop-us.vive.com' === $host || 'shop' === $host || 'shop-us' === $host ) {
		$file = theme_vive_find_index( $root . '/shop-us', $clean );
		if ( ! $file && preg_match( '#^/collections/[^/]+/products/(?<slug>[^/]+)/?$#i', $clean, $m ) ) {
			$file = theme_vive_find_index( $root . '/shop-us/products', '/' . $m['slug'] . '/' );
		}
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/shop-us.vive.com', '/' === $clean ? '/home/' : $clean ); }
		return $file;
	}
	if ( 'blog.vive.com' === $host || 'blog' === $host ) {
		$file = theme_vive_find_index( $root . '/footer-sites/blog.vive.com/us', $clean );
		if ( ! $file && 0 === strpos( $clean, '/us/' ) ) {
			$file = theme_vive_find_index( $root . '/footer-sites/blog.vive.com', $clean );
		}
		if ( ! $file ) {
			$file = theme_vive_find_index( $root . '/footer-sites/blog.vive.com', '/' === $clean ? '/us/' : $clean );
		}
		if ( $file ) { return $file; }
	}
	$candidates = array( $clean );
	if ( preg_match( '#-html/?$#i', $clean ) ) {
		$candidates[] = preg_replace( '#-html/?$#i', '.html', $clean );
		$candidates[] = preg_replace( '#-html/?$#i', '.html/', $clean );
	}
	if ( '/' === $clean ) {
		$candidates[] = '/home/';
		$candidates[] = '/us/';
	}
	foreach ( $candidates as $c ) {
		$file = theme_vive_find_index( $root . '/footer-sites/' . $host, $c );
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/' . $host . '/us', $c ); }
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/' . $host . '.vive.com', $c ); }
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/' . $host . '.vive.com/us', $c ); }
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/www.' . $host, $c ); }
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/www.' . $host . '.vive.com', $c ); }
		if ( $file ) { return $file; }
	}
	// Fallback subdomain index
	$sub_defaults = array( '/', '/us/', '/home/', '/en-us/download/', '/resources/', '/download/' );
	foreach ( $sub_defaults as $sd ) {
		$file = theme_vive_find_index( $root . '/footer-sites/' . $host, $sd );
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/' . $host . '.vive.com', $sd ); }
		if ( ! $file ) { $file = theme_vive_find_index( $root . '/footer-sites/www.' . $host, $sd ); }
		if ( $file ) { return $file; }
	}
	return false;
}

function theme_vive_permalink_path( $path ) {
	return theme_vive_clean_slug_path( $path );
}

function theme_vive_host_slug( $host ) {
	$host = strtolower( preg_replace( '#^www\.#', '', $host ) );
	if ( 'vive.com' === $host ) { return 'www'; }
	if ( 'shop-us.vive.com' === $host ) { return 'shop'; }
	return preg_replace( '#\.vive\.com$#', '', $host );
}

function theme_vive_slug_host( $slug ) {
	$slug = strtolower( sanitize_title( $slug ) );
	if ( 'www' === $slug ) { return 'vive.com'; }
	if ( 'shop' === $slug || 'shop-us' === $slug ) { return 'shop-us.vive.com'; }
	if ( 'vr4impact' === $slug ) { return 'vr4impact.com'; }
	return $slug . '.vive.com';
}

function theme_vive_permalink( $host, $path ) {
	$clean_path = theme_vive_clean_slug_path( $path );
	$slug = theme_vive_host_slug( $host );
	if ( 'www' === $slug ) {
		if ( '/' === $clean_path ) {
			return home_url( '/' );
		}
		return home_url( $clean_path );
	}
	if ( 'shop' === $slug ) {
		return home_url( '/shop' . ( '/' === $clean_path ? '/' : $clean_path ) );
	}
	return home_url( '/' . $slug . ( '/' === $clean_path ? '/' : $clean_path ) );
}

function theme_vive_missing_route_fallback( $path ) {
	if ( preg_match( '#^/accessor(?:y|ies)(?:/|$)#i', $path ) ) { return home_url( '/accessory/' ); }
	if ( preg_match( '#^/(?:product|products|collections)(?:/|$)#i', $path ) ) { return home_url( '/product/' ); }
	if ( preg_match( '#^/blog(?:/|$)#i', $path ) ) { return home_url( '/blog/' ); }
	return home_url( '/' );
}

function theme_vive_local_link( $url ) {
	$url = trim( (string) $url );
	// Leave client-side template placeholders intact. They are resolved by JS
	// after load; treating them as literal URLs turns every card into home_url().
	if ( false !== strpos( $url, '${' ) || false !== strpos( $url, '{{' ) ) {
		return $url;
	}
	if ( ! $url || '#' === $url[0] || 0 === strpos( $url, 'javascript:' ) || 0 === strpos( $url, 'tel:' ) || 0 === strpos( $url, 'mailto:' ) ) {
		return $url;
	}
	// Repair retired Shopify CTAs used by the Store and Best Deals snapshots.
	// Match by path so both absolute shop-us.vive.com links and local links work.
	$link_path = (string) wp_parse_url( 0 === strpos( $url, '/' ) ? $url : ( 0 === strpos( $url, '//' ) ? 'https:' . $url : $url ), PHP_URL_PATH );
	if ( '/us/product/vive-eagle/stores' === strtolower( untrailingslashit( $link_path ) ) ) {
		return home_url( '/shop/' );
	}
	$shop_cta_routes = array(
		'/product/2104030'                         => '/accessory/controller/',
		'/collections/on-sale/products/2104030'   => '/accessory/controller/',
		'/product/21071655'                        => '/product/vive-flow/overview/',
		'/collections/on-sale/products/21071655'   => '/product/vive-flow/overview/',
		'/pages/vive-focus-vision'                 => '/product/vive-focus-vision/overview/',
		'/pages/b2b-landing-page'                  => '/business/',
		'/promotion/best-vr-deals'                 => '/shop/pages/best-vr-deals/',
		'/us/promotion/best-vr-deals'              => '/shop/pages/best-vr-deals/',
	);
	$route_key = strtolower( untrailingslashit( $link_path ) );
	if ( isset( $shop_cta_routes[ $route_key ] ) ) {
		return home_url( $shop_cta_routes[ $route_key ] );
	}
	// Retired campaign/tracking destinations still embedded in homepage cards.
	if ( preg_match( '#(?:^|//[^/]+)/(?:roundtable|click-detail(?:-bestvrdeals)?)/?(?:[?\#].*)?$#i', $url ) ) {
		return home_url( '/' );
	}
	if ( preg_match( '#(?:^|//[^/]+)/(?:contact(?:/hc/[^/]+)?|lien-he)/?(?:[?\#].*)?$#i', $url ) ) {
		return home_url( '/contact/' );
	}
	if ( preg_match( '#(?:^|//[^/]+)/(?:product/(?:overview|specs?)(?:-[0-9]+)?|(?:19|20)[0-9]{2}/[0-9]{2}|category/[^?\#]+|tag/[^?\#]+|download|location-based-experiences|vive-desk|vive-streaming)/?(?:[?\#].*)?$#i', $url ) ) {
		$path = (string) wp_parse_url( 0 === strpos( $url, '/' ) ? $url : ( 0 === strpos( $url, '//' ) ? 'https:' . $url : $url ), PHP_URL_PATH );
		if ( preg_match( '#^/product/#i', $path ) ) { return home_url( '/product/' ); }
		if ( preg_match( '#^/(?:category|tag|(?:19|20)[0-9]{2}/)#i', $path ) ) { return home_url( '/blog/' ); }
		return home_url( '/support/' );
	}
	// Giữ nguyên link VIVERSE chính thức
	if ( preg_match( '#(?:^|//|\.)viverse\.com#i', $url ) ) {
		if ( 0 === strpos( $url, '//' ) ) {
			return 'https:' . $url;
		}
		return $url;
	}
	// Giữ nguyên link Zalo, Google Maps và các mạng xã hội/kênh liên hệ chính thức
	if ( preg_match( '#(?:^|//|\.)(?:zalo\.me|google\.com|goo\.gl|facebook\.com|youtube\.com|instagram\.com|tiktok\.com|discord\.gg|discord\.com)#i', $url ) ) {
		if ( 0 === strpos( $url, '//' ) ) {
			return 'https:' . $url;
		}
		return $url;
	}
	if ( preg_match( '#^/(?:rest|redirect)/#i', $url ) ) {
		return $url;
	}
	// Xử lý link nội bộ bắt đầu bằng /
	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		// The archived support tree contains thousands of expensive legacy pages.
		// Keep navigation local and stable at the working support landing page.
		if ( preg_match( '#^/support/[^/]+/.+#i', $url ) ) {
			return home_url( '/support/' );
		}
		if ( preg_match( '#^/(?:contact|lien-he)/?$#i', $url ) ) {
			return home_url( '/contact/' );
		}
		if ( 0 === strpos( $url, '/shop/' ) || '/shop' === $url ) {
			$subpath = substr( $url, 5 );
			return theme_vive_permalink( 'shop-us.vive.com', $subpath );
		}
		if ( preg_match( '#^/collections/[^/]+/products/(?<slug>[^/?\#]+)/?$#i', $url, $m ) ) {
			return home_url( '/shop/products/' . $m['slug'] . '/' );
		}
		if ( preg_match( '#^/shop/collections/[^/]+/products/(?<slug>[^/?\#]+)/?$#i', $url, $m ) ) {
			return home_url( '/shop/products/' . $m['slug'] . '/' );
		}
		if ( preg_match( '#^/collections(?:/all)?/?$#i', $url ) ) {
			return home_url( '/shop/collections/' );
		}
		if ( preg_match( '#^/collections/(?<subslug>[^/?\#]+)/?$#i', $url, $m ) ) {
			return home_url( '/shop/collections/' . $m['subslug'] . '/' );
		}
		if ( preg_match( '#^/products/(?<slug>[^/?\#]+)/?$#i', $url, $m ) ) {
			return home_url( '/shop/products/' . $m['slug'] . '/' );
		}
		if ( preg_match( '#^/(?<slug>business|developer|service|blog|mars|sync|viverse)(?<subpath>/.*)?$#i', $url, $m ) ) {
			$subpath = ! empty( $m['subpath'] ) ? $m['subpath'] : '/';
			return theme_vive_permalink( theme_vive_slug_host( $m['slug'] ), $subpath );
		}
		$clean = theme_vive_clean_slug_path( $url );
		if ( '/contact/' === $clean || '/contact' === $clean ) {
			return home_url( '/contact/' );
		}
		return theme_vive_permalink( 'vive.com', $clean );
	}
	$normalized = 0 === strpos( $url, '//' ) ? 'https:' . $url : $url;
	$parts = wp_parse_url( html_entity_decode( $normalized ) );
	$local_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	if ( ! empty( $parts['host'] ) && $local_host === strtolower( $parts['host'] ) ) {
		$local_path = isset( $parts['path'] ) ? $parts['path'] : '/';
		if ( preg_match( '#^/support/[^/]+/.+#i', $local_path ) ) { return home_url( '/support/' ); }
		return $normalized;
	}
	if ( ! empty( $parts['host'] ) && 'contact.vive.com' === strtolower( preg_replace( '#^www\.#', '', $parts['host'] ) ) ) {
		return home_url( '/contact/' );
	}
	if ( ! empty( $parts['host'] ) && ( 'shop-us.vive.com' === strtolower( preg_replace( '#^www\.#', '', $parts['host'] ) ) || 'shop' === strtolower( preg_replace( '#^www\.#', '', $parts['host'] ) ) ) ) {
		$raw_path = isset( $parts['path'] ) ? $parts['path'] : '/';
		if ( preg_match( '#^/collections/[^/]+/products/(?<slug>[^/?\#]+)/?$#i', $raw_path, $m ) ) {
			return home_url( '/shop/products/' . $m['slug'] . '/' );
		}
		if ( preg_match( '#^/collections(?:/all)?/?$#i', $raw_path ) ) {
			return home_url( '/shop/collections/' );
		}
		if ( preg_match( '#^/collections/(?<subslug>[^/?\#]+)/?$#i', $raw_path, $m ) ) {
			return home_url( '/shop/collections/' . $m['subslug'] . '/' );
		}
		if ( preg_match( '#^/products/(?<slug>[^/?\#]+)/?$#i', $raw_path, $m ) ) {
			return home_url( '/shop/products/' . $m['slug'] . '/' );
		}
		$clean_path = theme_vive_clean_slug_path( $raw_path );
		return theme_vive_permalink( 'shop-us.vive.com', $clean_path );
	}
	if ( empty( $parts['host'] ) || ! preg_match( '#(^|\.)vive\.com$#i', $parts['host'] ) ) { 
		return home_url( '/' ); 
	}
	$path = isset( $parts['path'] ) ? $parts['path'] : '/';
	if ( preg_match( '#^/collections/[^/]+/products/(?<slug>[^/?\#]+)/?$#i', $path, $m ) ) {
		return home_url( '/shop/products/' . $m['slug'] . '/' );
	}
	if ( preg_match( '#^/collections(?:/all)?/?$#i', $path ) ) {
		return home_url( '/shop/collections/' );
	}
	if ( preg_match( '#^/collections/(?<subslug>[^/?\#]+)/?$#i', $path, $m ) ) {
		return home_url( '/shop/collections/' . $m['subslug'] . '/' );
	}
	if ( preg_match( '#^/products/(?<slug>[^/?\#]+)/?$#i', $path, $m ) ) {
		return home_url( '/shop/products/' . $m['slug'] . '/' );
	}
	$clean_path = theme_vive_clean_slug_path( $path );
	if ( 'support.vive.com' === strtolower( preg_replace( '#^www\.#', '', $parts['host'] ) ) && preg_match( '#^/[^/]+/.+#', $clean_path ) ) {
		return home_url( '/support/' );
	}
	$local = theme_vive_permalink( $parts['host'], $clean_path );
	if ( ! empty( $parts['query'] ) ) { $local .= '?' . $parts['query']; }
	if ( ! empty( $parts['fragment'] ) ) { $local .= '#' . $parts['fragment']; }
	return $local;
}

function theme_vive_asset_url( $path ) {
	$path = '/' . ltrim( html_entity_decode( $path ), '/' );
	$file = get_template_directory() . '/assets' . rawurldecode( strtok( $path, '?#' ) );
	return is_file( $file ) ? get_template_directory_uri() . '/assets' . $path : '';
}

function theme_vive_source_host( $file ) {
	$normalized = str_replace( '\\', '/', $file );
	if ( preg_match( '#/footer-sites/(?<host>[^/]+)/#', $normalized, $m ) ) { return $m['host']; }
	if ( false !== strpos( $normalized, '/shop-us/' ) ) { return 'shop-us.vive.com'; }
	return 'www.vive.com';
}

/** Identify every blog route independently of which snapshot file serves it. */
function theme_vive_is_blog_request() {
	$request_path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home_path && '/' !== $home_path && 0 === strpos( (string) $request_path, $home_path ) ) {
		$request_path = substr( (string) $request_path, strlen( rtrim( $home_path, '/' ) ) );
	}
	return (bool) preg_match( '#^/blog(?:/|$)#i', (string) $request_path );
}

/** Replace a mirrored page's header/footer with the exact homepage chrome. */
function theme_vive_use_homepage_chrome( $html ) {
	static $home_header = null;
	static $home_footer = null;
	if ( null === $home_header || null === $home_footer ) {
		// Resolve the homepage through the same source lookup as the router. On
		// production the active mirror may live in a fallback tree rather than
		// pages/us, even though the homepage itself renders successfully.
		$home_file = theme_vive_page_file( 'vive.com', '/' );
		if ( ! $home_file ) {
			$home_file = theme_vive_find_index( get_template_directory() . '/pages/us', '/' );
		}
		$home_html = $home_file && is_readable( $home_file ) ? file_get_contents( $home_file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$home_header = '';
		$home_footer = '';
		if ( preg_match( '#<header\b[^>]*>.*?</header>#is', $home_html, $match ) ) { $home_header = $match[0]; }
		if ( preg_match( '#<footer\b[^>]*>.*?</footer>#is', $home_html, $match ) ) { $home_footer = $match[0]; }
	}
	if ( $home_header ) {
		if ( preg_match( '#(?:<!-- BEGIN sections: header-group -->|Phần BẮT ĐẦU:\s*nhóm tiêu đề).*?(?:<!-- END sections: header-group -->|Phần KẾT THÚC:\s*nhóm tiêu đề)#is', $html ) ) {
			$html = preg_replace( '#(?:<!-- BEGIN sections: header-group -->|Phần BẮT ĐẦU:\s*nhóm tiêu đề).*?(?:<!-- END sections: header-group -->|Phần KẾT THÚC:\s*nhóm tiêu đề)#is', $home_header, $html, 1 );
		} elseif ( preg_match( '#<div\b[^>]*class=["\'][^"\']*\bsite-header-container\b[^"\']*["\'][^>]*>.*?</div>\s*</div>#is', $html ) ) {
			$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bsite-header-container\b[^"\']*["\'][^>]*>.*?</div>\s*</div>#is', $home_header, $html, 1 );
		} elseif ( preg_match( '#<header\b[^>]*>.*?</header>#is', $html ) ) {
			$html = preg_replace( '#<header\b[^>]*>.*?</header>#is', $home_header, $html, 1 );
		}
		// Clean up any remaining Shopify header fragments and comment remnants
		$html = preg_replace( '#<!-- BEGIN sections: header-group -->.*?<!-- END sections: header-group -->#is', '', $html );
		$html = preg_replace( '#(?:Phần BẮT ĐẦU|Phần KẾT THÚC):\s*nhóm tiêu đề#u', '', $html );
		$html = preg_replace( '#(?:Phần BẮT ĐẦU|Phần KẾT THÚC):\s*nhóm chân trang#u', '', $html );
		$html = preg_replace( '#Khối ứng dụng BEGIN:[^\n<>]*#u', '', $html );
		$html = preg_replace( '#KẾT THÚC khối ứng dụng#u', '', $html );
		$html = preg_replace( '#Nội dung tiêu đề tiêu chuẩn Nav20 từ trang chủ#u', '', $html );
	}
	if ( $home_footer ) {
		if ( preg_match( '#(?:<!-- BEGIN sections: footer-group -->|Phần BẮT ĐẦU:\s*nhóm chân trang).*?(?:<!-- END sections: footer-group -->|Phần KẾT THÚC:\s*nhóm chân trang)#is', $html ) ) {
			$html = preg_replace( '#(?:<!-- BEGIN sections: footer-group -->|Phần BẮT ĐẦU:\s*nhóm chân trang).*?(?:<!-- END sections: footer-group -->|Phần KẾT THÚC:\s*nhóm chân trang)#is', $home_footer, $html, 1 );
		} elseif ( preg_match( '#<footer\b[^>]*>.*?</footer>#is', $html ) ) {
			$html = preg_replace( '#<footer\b[^>]*>.*?</footer>#is', $home_footer, $html, 1 );
		}
	}
	// Inject homepage navigation assets if missing
	if ( false === strpos( $html, 'dist_2020/new-styles.css' ) ) {
		$nav_css = '<link rel="stylesheet" type="text/css" href="/static/app/dist_2020/other-styles.css?v=202608100232">' . "\n"
			. '<link rel="stylesheet" type="text/css" href="/static/app/dist_2020/original-styles.css?v=202608100232">' . "\n"
			. '<link rel="stylesheet" type="text/css" href="/static/app/dist_2020/new-styles.css?v=202608100232">' . "\n"
			. '<link rel="stylesheet" type="text/css" href="/media/filer_public/fed-assets/revamp-20/css/critical.css?v=202407301200">' . "\n";
		if ( false !== strpos( $html, '</head>' ) ) {
			$html = str_replace( '</head>', $nav_css . '</head>', $html );
		}
	}
	if ( false === strpos( $html, 'dist_2020/app.js' ) ) {
		$nav_js = '<script type="text/javascript" src="/static/js/vendor/jquery-3.6.3.min.js"></script>' . "\n"
			. '<script type="text/javascript" src="/static/js/vendor/jquery-migrate-3.4.0.min.js"></script>' . "\n"
			. '<script defer type="text/javascript" src="/static/app/dist_2020/vendor_1.js?v=202608100232"></script>' . "\n"
			. '<script defer type="text/javascript" src="/static/app/dist_2020/vendor_2.js?v=202608100232"></script>' . "\n"
			. '<script defer type="text/javascript" src="/static/app/dist_2020/vendor_3.js?v=202608100232"></script>' . "\n"
			. '<script defer type="text/javascript" src="/static/app/dist_2020/app.js?v=202608100232"></script>' . "\n"
			. '<script defer src="/media/filer_public/fed-assets/revamp-20/js/default.js?v=202309011500"></script>' . "\n";
		if ( false !== strpos( $html, '</body>' ) ) {
			$html = str_replace( '</body>', $nav_js . '</body>', $html );
		}
	}
	return $html;
}

/** Remove the retired external-store mini-cart from mirrored headers. */
function theme_vive_remove_header_cart( $html ) {
	return preg_replace(
		'#<a\b[^>]*class=["\'][^"\']*\bnav20-mini-cart\b[^"\']*["\'][^>]*>.*?</a>#is',
		'',
		(string) $html
	);
}

function theme_vive_translate_html( $html, $include_auto = true ) {
	$translations = theme_vive_translation_map( $include_auto );
	if ( empty( $translations ) || ! is_array( $translations ) ) { return $html; }
	// Some accessory snapshots contain a literal, unescaped "< Accessories".
	// Encode the back arrow first so the text-node translator does not mistake
	// it for the beginning of an HTML tag. This repairs every accessory page.
	$html = preg_replace( '#(>\s*)<\s*Accessories(\s*<)#i', '$1&lt; Accessories$2', $html );
	$protected = array();
	$html = preg_replace_callback( '#<(script|style|noscript|template)\b[^>]*>.*?</\1>#is', static function ( $m ) use ( &$protected ) {
		$key = '%%THEME_VIVE_PROTECTED_' . count( $protected ) . '%%';
		$protected[ $key ] = $m[0];
		return $key;
	}, $html );
	$html = preg_replace_callback( '#>(?<text>[^<>]+)<#s', static function ( $m ) use ( $translations ) {
		$raw = $m['text'];
		$key = trim( html_entity_decode( $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( '' === $key ) { return '>' . $raw . '<'; }
		$translated = isset( $translations[ $key ] ) ? $translations[ $key ] : null;
		if ( null === $translated ) {
			$norm_key = preg_replace( '/\s+/', ' ', $key );
			if ( isset( $translations[ $norm_key ] ) ) {
				$translated = $translations[ $norm_key ];
			}
		}
		if ( null === $translated || '' === $translated ) { return '>' . $raw . '<'; }
		$left  = substr( $raw, 0, strlen( $raw ) - strlen( ltrim( $raw ) ) );
		$right = substr( $raw, strlen( rtrim( $raw ) ) );
		return '>' . $left . esc_html( $translated ) . $right . '<';
	}, $html );
	$html = preg_replace_callback( '#(?<prefix>\b(?:title|placeholder|aria-label|alt|value)=["\'])(?<text>[^"\']+)(?<suffix>["\'])#i', static function ( $m ) use ( $translations ) {
		$key = html_entity_decode( trim( $m['text'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$translated = isset( $translations[ $key ] ) ? $translations[ $key ] : null;
		if ( null === $translated ) {
			$norm_key = preg_replace( '/\s+/', ' ', $key );
			if ( isset( $translations[ $norm_key ] ) ) {
				$translated = $translations[ $norm_key ];
			}
		}
		return null !== $translated && '' !== $translated ? $m['prefix'] . esc_attr( $translated ) . $m['suffix'] : $m[0];
	}, $html );
	$html = preg_replace_callback( '#(<meta\b[^>]*?\b(?:name|property)=["\'](?:description|og:title|og:description|twitter:title|twitter:description)["\'][^>]*?\bcontent=["\'])(?<text>[^"\']+)(["\'])#i', static function ( $m ) use ( $translations ) {
		$key = html_entity_decode( trim( $m['text'] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$translated = isset( $translations[ $key ] ) ? $translations[ $key ] : null;
		if ( null === $translated ) {
			$norm_key = preg_replace( '/\s+/', ' ', $key );
			if ( isset( $translations[ $norm_key ] ) ) {
				$translated = $translations[ $norm_key ];
			}
		}
		return null !== $translated && '' !== $translated ? $m[1] . esc_attr( $translated ) . $m[3] : $m[0];
	}, $html );
	$html = strtr( $html, $protected );
	// Repair legacy machine translations of VIVE trademark/model names. This
	// also covers names embedded inside longer translated menu labels.
	$html = str_ireplace(
		array( 'Tầm nhìn tập trung VIVE', 'VIVE Tập Trung 3', 'Dòng chảy VIVE', 'Đại bàng VIVE', 'TRỰC TIẾP XR Ưu tú', 'kính VR VIVE Pro 2', 'Nghệ thuật VIVE', 'Sao Hỏa SỐNG', 'Đồng bộ hóa VIVE' ),
		array( 'VIVE Focus Vision', 'VIVE Focus 3', 'VIVE Flow', 'VIVE Eagle', 'VIVE XR Elite', 'VIVE Pro 2 Headset', 'VIVE Arts', 'VIVE Mars', 'VIVE Sync' ),
		$html
	);
	// The upstream styles use selectors such as [dir="ltr"] to pair
	// left:50% with translateX(-50%). Several archived pages omit dir, which
	// leaves centered content shifted half a viewport to the left on mobile.
	// Normalize both document attributes for every mirrored page.
	$html = preg_replace_callback( '#<html\b(?<attrs>[^>]*)>#i', static function ( $match ) {
		$attrs = preg_replace( '#\s+(?:lang|dir)\s*=\s*(?:["\'][^"\']*["\']|[^\s>]+)#i', '', $match['attrs'] );
		return '<html' . $attrs . ' lang="vi" dir="ltr">';
	}, $html, 1 );
	// Đổi tất cả quốc gia, thành phố và vùng lãnh thổ sang Việt Nam
	$country_map = array(
		'Đài Loan'          => 'Việt Nam',
		'ĐÀI LOAN'          => 'VIỆT NAM',
		'đài loan'          => 'việt nam',
		'Hoa Kỳ'            => 'Việt Nam',
		'HOA KỲ'            => 'VIỆT NAM',
		'hoa kỳ'            => 'việt nam',
		'nước Mỹ'           => 'Việt Nam',
		'Hàn Quốc'          => 'Việt Nam',
		'HÀN QUỐC'          => 'VIỆT NAM',
		'hàn quốc'          => 'việt nam',
		'Nhật Bản'          => 'Việt Nam',
		'NHẬT BẢN'          => 'VIỆT NAM',
		'nhật bản'          => 'việt nam',
		'Trung Quốc'        => 'Việt Nam',
		'TRUNG QUỐC'        => 'VIỆT NAM',
		'trung quốc'        => 'việt nam',
		'Vương quốc Anh'    => 'Việt Nam',
		'nước Anh'          => 'Việt Nam',
		'nước Pháp'         => 'Việt Nam',
		'nước Đức'          => 'Việt Nam',
		'nước Úc'           => 'Việt Nam',
		'Hồng Kông'         => 'Việt Nam',
		'Liên minh Châu Âu' => 'Việt Nam',
		'Trung Đông'        => 'Việt Nam',
		'Đông Nam Á'        => 'Việt Nam',
		'nước Nga'          => 'Việt Nam',
		'Đài Bắc'           => 'Việt Nam',
		'Taipei'            => 'Việt Nam',
		'Taiwan'            => 'Việt Nam',
		'United States'     => 'Việt Nam',
		'United Kingdom'    => 'Việt Nam',
		'North America'     => 'Việt Nam',
		'Buy Khôngw'        => 'Mua ngay',
		'HTC America, Inc.' => 'VIVE Việt Nam',
		'HTC Corporation'   => 'VIVE Việt Nam',
		'HTC America'       => 'VIVE Việt Nam',
	);
	$html = strtr( $html, $country_map );
	$html = preg_replace( '/\b(?:United States|United Kingdom|Australia|Canada|Deutschland|Germany|France|Japan|Korea|Taiwan|China|Russia|USA|UK|Taipei|America|Taoyuan|Bellevue|Seattle)\b/i', 'Việt Nam', $html );
	$html = str_replace( 'Việt Nam Việt Nam', 'Việt Nam', $html );

	// Chuẩn hóa địa chỉ nước ngoài sang địa chỉ văn phòng VIVE Việt Nam
	$html = preg_replace( '/(?:308\s+Occidental\s+Ave(?:\s+S)?|No\.\s*23,?\s*Xinghua\s*Rd|Taoyuan\s*City|Seattle,?\s*WA\s*\d{5})[^<>\n]*/i', '226 Đường Láng, Phường Thịnh Quang, Quận Đống Đa, Hà Nội', $html );

	// Chuẩn hóa số điện thoại quốc tế / US sang Hotline 1900.63.8400
	$html = preg_replace( '#\bhref=["\']tel:(?:\+?1)?(?:800|888|877|866|855|844)\d{7}["\']#i', 'href="tel:1900638400"', $html );
	$html = preg_replace( '/(?:\+1[\s.-]?)?\(?(?:800|888|877|866|855|844)\)?[\s.-]?\d{3}[\s.-]?\d{4}/', '1900.63.8400', $html );

	// Dropdown quốc gia / vị trí: chuyển toàn bộ các thẻ select chọn quốc gia / ngôn ngữ thành Việt Nam
	$html = preg_replace( '#<select\b[^>]*?\b(?:id|name)=["\'](?:language|country|region)["\'][^>]*>.*?</select>#is', '<select id="language" name="language"><option data-short="vi" value="vi" selected="selected">Việt Nam</option></select>', $html );
	$html = preg_replace( '#<option[^>]*?>\s*(?:USA|Hoa Kỳ|United States|한국|Australia|Canada\s*-\s*(?:En|Français)|Canada|Deutschland|European Union|France|Hong Kong|Middle East(?:\s*Arabic)?|New Zealand|Southeast Asia|Ukraine|United Kingdom|СНГ|中国|台灣|日本|Đài Loan)\s*</option>#i', '<option data-short="vi" value="vi" selected="selected">Việt Nam</option>', $html );

	// Chuyển toàn bộ email sang contact@vive.com.vn
	$html = preg_replace( '#\bhref=["\']mailto:[^"\']+["\']#i', 'href="mailto:contact@vive.com.vn"', $html );
	$html = preg_replace( '/\b[A-Za-z0-9._%+-]+@(?!(?:vive\.com\.vn)\b)(?:htc|vive|digitalriver|sykes|sitel|[a-zA-Z0-9.-]+)\.[A-Za-z]{2,}\b/i', 'contact@vive.com.vn', $html );
	$html = str_replace( 'contact@vive.com.vn.vn', 'contact@vive.com.vn', $html );
	return $html;
}

function theme_vive_render( $file ) {
	if ( ! $file || ! is_readable( $file ) ) { return ''; }
	$html = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$source_host = theme_vive_source_host( $file );
	$is_shop_mirror = 'shop-us.vive.com' === $source_host || 'shop' === $source_host;
	if ( 'blog.vive.com' === $source_host || theme_vive_is_blog_request() || 'shop-us.vive.com' === $source_host || 'shop' === $source_host ) {
		$html = theme_vive_use_homepage_chrome( $html );
		if ( 'blog.vive.com' === $source_host || theme_vive_is_blog_request() ) {
			$html = theme_vive_blog_prepare_html( $html, $file );
			global $theme_vive_blog_post;
			if ( $theme_vive_blog_post instanceof WP_Post ) { $html = theme_vive_blog_render_html( $html, $theme_vive_blog_post ); }
		}
	}
	$html = theme_vive_remove_header_cart( $html );
	// Strip third-party widget scripts & preconnects (getsitecontrol, socialchat, loup, VWO blocker, Marketo modal, Shopify Forms popup, RestockRocket)
	$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bshopify-app-block\b[^"\']*["\'][^>]*>.*?</div>#is', '', $html );
	$html = preg_replace( '#(?:Cấu hình và thiết lập JS|JS configuration and setup)#u', '', $html );
	$html = preg_replace( '#<script\b[^>]*?(?:getsitecontrol|socialchat|loup\.ai|marketo\.com|MktoForms2|shopify-forms|forms-2526|01a048ca-fd00-7dc3-902b-36c103d51f9e|restockrocket|RestockRocket)[^>]*>.*?</script>#is', '', $html );
	$html = preg_replace_callback( '#<script\b[^>]*>.*?</script>#is', static function ( $m ) {
		return ( false !== strpos( $m[0], '_RestockRocketConfig' ) || false !== strpos( $m[0], 'RestockRocket' ) || false !== strpos( $m[0], 'BndlrScriptAppended' ) ) ? '' : $m[0];
	}, (string) $html );
	$html = preg_replace( '#<link\b[^>]*?(?:getsitecontrol|socialchat|loup\.ai|dev\.visualwebsiteoptimizer\.com|marketo\.com|shopify-forms|restockrocket)[^>]*>#i', '', $html );
	$html = preg_replace( '#<script\b[^>]*?id=[\'"]vwoCode[\'"][^>]*>.*?</script>#is', '', $html );
	$html = preg_replace( '#<style\b[^>]*?id=[\'"]_vis_opt_path_hides[\'"][^>]*>.*?</style>#is', '', $html );
	$html = preg_replace( '#<section\b[^>]*?class=["\'][^"\']*section-marketo-popup[^"\']*["\'][^>]*>.*?</section>#is', '', $html );
	$html = preg_replace( '#<script\b[^>]*?>\s*function\s+loadPreOrderForm\b.*?loadPreOrderForm\(\)[;\s]*</script>#is', '', $html );
	$html = preg_replace( '#<shopify-forms-embed\b[^>]*>.*?</shopify-forms-embed>#is', '', $html );
	// Do not leak local/staging visits into the analytics containers embedded in
	// the upstream snapshot. The production owner can install its own GA4/GTM.
	$html = preg_replace( '#<script\b[^>]*?(?:googletagmanager\.com|google-analytics\.com)[^>]*>.*?</script>#is', '', $html );
	$html = preg_replace( '#<script\b[^>]*>(?:(?!</script>).)*\bgtm\.start\b(?:(?!</script>).)*</script>#is', '', $html );
	$html = preg_replace( '#<noscript\b[^>]*>\s*<iframe\b[^>]*(?:googletagmanager\.com|google-analytics\.com)[^>]*>.*?</iframe>\s*</noscript>#is', '', $html );
	// Remove Shopify bootstrap files that only work inside its hosted checkout.
	$html = preg_replace( '#<script\b[^>]*\bsrc=["\'][^"\']*(?:/checkouts/internal/preloads\.js|shop\.app/checkouts/)[^"\']*["\'][^>]*>\s*</script>#is', '', $html );
	if ( $is_shop_mirror ) {
		// The archived homepage has a one-character casing typo. Its component
		// scripts consistently read window.KROWN, so restore the original global.
		$html = preg_replace( '#(<script\b[^>]*>\s*)Krown\s*=#i', '$1KROWN =', $html );
		// Shopify telemetry, hosted checkout and account helpers cannot operate on
		// the mirrored origin. They do not render storefront content, but they do
		// generate CORS, CSP, missing pixel and rejected-promise console errors.
		$html = preg_replace(
			'#<script\b[^>]*\bsrc=["\'][^"\']*(?:/cdn/wpm/|/cdn/shopifycloud/(?:privacy-banner|perf-kit|shop-js|portable-wallets|storefront/)|cdn\.shopify\.com/(?:shopifycloud/shop-js|storefront/)|shop_events_listener|event_observer_reporter|standard-actions\.js)[^"\']*["\'][^>]*>\s*</script>#is',
			'',
			$html
		);
		$html = preg_replace_callback( '#<script\b[^>]*>.*?</script>#is', static function ( $match ) {
			$script = $match[0];
			$markers = array(
				'cdn.shopify.com/shopifycloud/storefront',
				'window.ShopifyAnalytics',
				'window.__TREKKIE_SHIM_QUEUE',
				'var wpmLoader=',
				'shopify.event_observer.bootstrap',
				'window.ShopifyPay',
				'shopify.dynamic_checkout',
				'portableWallets',
				'cart-shipping-calculator-pro',
				'cdn-bundler.nice-team.net',
			);
			foreach ( $markers as $marker ) {
				if ( false !== stripos( $script, $marker ) ) { return ''; }
			}
			return $script;
		}, (string) $html );
		// This legacy VIVE homepage bundle aborts immediately because its
		// ScrollMagic dependency is not part of the shop snapshot. No shop UI is
		// initialized by it, so omit the dead request and exception.
		$html = preg_replace(
			'#<script\b[^>]*\bsrc=["\'][^"\']*/revamp-20/js/default\.js[^"\']*["\'][^>]*>\s*</script>#is',
			'',
			$html
		);
		$html = preg_replace( '#<link\b[^>]*(?:shop\.app|shopify-accelerated-checkout|portable-wallets)[^>]*>#i', '', $html );
		$html = preg_replace( '#<style\b[^>]*\bid=["\']shopify-accelerated-checkout[^"\']*["\'][^>]*>.*?</style>#is', '', $html );
		// The header cart is intentionally absent on the local mirror. Guarding
		// this obsolete observer by removing it avoids observing a null node and
		// cannot change visible content.
		$html = preg_replace(
			'#<script\b[^>]*>\s*new\s+MutationObserver\s*\(.*?\.observe\s*\(\s*document\.querySelector\s*\(\s*["\']\.cart-form["\']\s*\).*?</script>#is',
			'',
			$html
		);
	}
	// These snapshot tags point at video pages rather than JavaScript files;
	// browsers otherwise try to parse the returned HTML as JavaScript.
	$html = preg_replace( '#<script\b[^>]*\bsrc=["\'](?:(?:https?:)?//)?(?:www\.)?(?:youtube\.com/watch|player\.youku\.com/jsapi)[^"\']*["\'][^>]*>\s*</script>#is', '', $html );
	// Repair old downloader output where an asset proxy prefix was accidentally
	// concatenated in front of an already complete absolute URL.
	$html = preg_replace( '#(?:https?:)?//[^/"\']+/wp-content/themes/theme_vive/footer-sites/assets/[^"\']*?(?<url>https?://[^"\']+)#i', '$1', $html );
	$html = preg_replace( '#/wp-content/themes/theme_vive/footer-sites/assets/[^"\']*?(?<url>https?://[^"\']+)#i', '$1', $html );
	// Serve crawled Shopify images locally. The shop homepage can come from the
	// legacy footer-sites tree while its refreshed assets live in shop-us/assets.
	$html = preg_replace_callback( '#(?:(?:https?:)?//)shop-us\.vive\.com(?<path>/cdn/(?:shop|fonts)/[^"\'\s<>\),;]+)#i', static function ( $match ) {
		$asset_path = rawurldecode( (string) wp_parse_url( $match['path'], PHP_URL_PATH ) );
		$local_file = get_template_directory() . '/shop-us/assets/shop-us.vive.com' . $asset_path;
		if ( ! is_file( $local_file ) ) { return $match[0]; }
		$query = (string) wp_parse_url( $match['path'], PHP_URL_QUERY );
		$url = get_template_directory_uri() . '/shop-us/assets/shop-us.vive.com' . $asset_path;
		return $query ? $url . '?' . $query : $url;
	}, $html );
	$html = preg_replace_callback( '#(?:(?:https?:)?//(?<h>(?:[^/]+\.)?vive\.com))?(?<p>/(?:static|media|express_static|express_media)/[^"\'\s<>\),;]+)#i', static function ( $m ) use ( $source_host ) {
		$local = theme_vive_asset_url( $m['p'] );
		if ( $local ) { return $local; }
		if ( 0 === strpos( $m['p'], '/express_' ) ) {
			return 'https://blog.vive.com' . $m['p'];
		}
		$host = ! empty( $m['h'] ) ? $m['h'] : ( 'blog.vive.com' === $source_host ? 'www.vive.com' : $source_host );
		return 'https://' . $host . $m['p'];
	}, $html );
	// The mirrored pages relied on a lazy-loader that is no longer available.
	// Activate its image attributes and give every <picture> a real img fallback.
	$html = preg_replace( '#\bdata-srcset\s*=#i', 'srcset=', $html );
	$html = preg_replace( '#\bdata-src\s*=#i', 'src=', $html );
	$html = preg_replace_callback( '#<picture\b[^>]*>.*?</picture>#is', static function ( $m ) {
		$picture = $m[0];
		if ( ! preg_match( '#<img\b(?<attrs>[^>]*)>#i', $picture, $image ) || preg_match( '#(?:^|\s)src\s*=#i', $image['attrs'] ) ) {
			return $picture;
		}
		if ( ! preg_match_all( '#<source\b[^>]*\bsrcset\s*=\s*["\']\s*(?<url>[^"\'\s,]+)#i', $picture, $sources ) || empty( $sources['url'] ) ) {
			return $picture;
		}
		$fallback = end( $sources['url'] );
		$replacement = '<img ' . $image['attrs'] . ' src="' . esc_url( $fallback ) . '">';
		return str_replace( $image[0], $replacement, $picture );
	}, $html );
	$html = theme_vive_products_prepare_html( $html, $file );
	global $theme_vive_current_product;
	if ( $theme_vive_current_product instanceof WC_Product && function_exists( 'theme_vive_product_render_html' ) ) {
		$html = theme_vive_product_render_html( $html, $theme_vive_current_product );
	}
	// Tự động thêm fallback onerror cho các thẻ img trong blog nếu chưa có
	$html = preg_replace_callback( '#<img\b([^>]*?)>#i', static function ( $m ) {
		$tag = $m[0];
		if ( false === strpos( $tag, 'onerror=' ) ) {
			$tag = preg_replace( '#<img\b#i', '<img onerror="this.onerror=null; this.src=\'https://blog.vive.com/express_media/images/Asset1-cover.scale-100.jpg\';"', $tag, 1 );
		}
		return $tag;
	}, $html );
	$html = preg_replace_callback( '#(<a\b[^>]*?\bhref\s*=\s*["\'])(?<u>[^"\']+)(["\'])#i', static function ( $m ) {
		if ( false !== strpos( $m['u'], '${' ) || false !== strpos( $m['u'], '{{' ) ) {
			return $m[0];
		}
		return $m[1] . esc_url( theme_vive_local_link( $m['u'] ) ) . $m[3];
	}, $html );
	$html = preg_replace_callback( '#(<form\b[^>]*?\baction\s*=\s*["\'])(?<u>[^"\']*)(["\'])#i', static function ( $m ) {
		if ( false !== strpos( $m['u'], '${' ) || false !== strpos( $m['u'], '{{' ) ) {
			return $m[0];
		}
		return $m[1] . esc_url( theme_vive_local_link( $m['u'] ) ) . $m[3];
	}, $html );
	// Use the same Vietnamese translation pipeline for every mirrored section.
	// Blog snapshots and WordPress-backed article content are already localized.
	// Loading the 27,000-entry automatic dictionary here adds substantial memory
	// and CPU cost and can exhaust smaller production PHP workers before a byte is
	// returned. The compact curated dictionary still translates shared navigation.
	$normalized_mirror_file = str_replace( '\\', '/', (string) $file );
	$is_shop = 'shop-us.vive.com' === $source_host || 'shop' === $source_host;
	$html = theme_vive_translate_html( $html, 'blog.vive.com' !== $source_host && ! $is_shop );
	// Use a clear Vietnamese hardware term in translated copy while leaving
	// technical attributes and URL slugs containing "dongle" untouched.
	$html = str_replace(
		array( 'Luồng VIVE', 'luồng VIVE', 'Dòng chảy VIVE', 'dòng chảy VIVE', 'Dongle nguồn đa cổng VIVE Flow', 'Dongle không dây VIVE', 'VIVE Wireless Dongle', 'VIVE Dongle', 'một dongle', 'Gioăng da PU VIVE XR Elite', 'VIVE XR Elite PU Leather Gasket', 'Show less', 'Show more', 'có thể gập lại<br/>kính thực tế ảo', 'có thể gập lại<br>kính thực tế ảo', '189 gam' ),
		array( 'VIVE Flow', 'VIVE Flow', 'VIVE Flow', 'VIVE Flow', 'Bộ chuyển đổi nguồn đa cổng VIVE Flow', 'Bộ thu phát không dây VIVE', 'Bộ thu phát không dây VIVE', 'Bộ thu phát không dây VIVE', 'một bộ thu phát không dây', 'Miếng đệm da PU cho VIVE XR Elite', 'Miếng đệm da PU cho VIVE XR Elite', 'Thu gọn', 'Xem thêm', 'Kính VR<br/>gập gọn', 'Kính VR<br>gập gọn', '189g' ),
		$html
	);
	// Give the two mirrored shop routes localized document/SEO titles. They do
	// not have WordPress Page records, so there is no database title to use.
	if ( 'shop-us.vive.com' === $source_host ) {
		$shop_file = str_replace( '\\', '/', (string) $file );
		$shop_title = '';
		if ( preg_match( '#/(?:shop-us|shop-us\.vive\.com)/home/index\.html?$#i', $shop_file ) ) {
			$shop_title = 'Cửa hàng VIVE Việt Nam | Kính thực tế ảo VR, AR và MR';
		} elseif ( preg_match( '#/(?:shop-us|shop-us\.vive\.com)/pages/best-vr-deals/index\.html?$#i', $shop_file ) ) {
			$shop_title = 'Ưu đãi kính thực tế ảo và phụ kiện HTC VIVE | VIVE Việt Nam';
		}
		if ( $shop_title ) {
			$html = preg_replace( '#<title\b[^>]*>.*?</title>#is', '<title>' . esc_html( $shop_title ) . '</title>', $html, 1 );
			$html = preg_replace( '#(<meta\b[^>]*(?:property|name)=["\'](?:og|twitter):title["\'][^>]*content=["\'])[^"\']*(["\'][^>]*>)#i', '$1' . esc_attr( $shop_title ) . '$2', $html );
		}
	}
	// Remove the retired Roundtable beta navigation item. Its archived link is
	// now only a target="_blank" link back to the homepage.
	$html = preg_replace(
		'#<div\b[^>]*class=["\'][^"\']*\bnav20-link-2-item\b[^"\']*["\'][^>]*>(?:(?!</div>).)*\bid=["\']func14774["\'](?:(?!</div>).)*</div>#is',
		'',
		$html
	);
	// WordPress has no database record for some valid mirror routes (for
	// example /product/.../overview/), so its main query is technically 404.
	// Preserve the source page metadata before Yoast runs and use it for these
	// virtual pages instead of emitting "Page not found".
	global $theme_vive_virtual_seo;
	$theme_vive_virtual_seo = array();
	if ( is_404() && ! theme_vive_is_blog_landing_request() ) {
		$virtual_title = '';
		$virtual_desc  = '';
		if ( preg_match( '#<title\b[^>]*>(?<text>.*?)</title>#is', $html, $virtual_match ) ) {
			$virtual_title = trim( wp_strip_all_tags( html_entity_decode( $virtual_match['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		}
		if ( preg_match( '#<meta\b[^>]*\bname=["\']description["\'][^>]*\bcontent=["\'](?<text>[^"\']+)["\']#i', $html, $virtual_match ) ) {
			$virtual_desc = trim( html_entity_decode( $virtual_match['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		}
		if ( $virtual_title ) {
			$request_path = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$theme_vive_virtual_seo = array(
				'title'       => $virtual_title,
				'description' => $virtual_desc ?: $virtual_title,
				'url'         => home_url( (string) $request_path ),
			);
		}
	}
	// One legacy article template contains no heading element at all. Keep its
	// database title available to screen readers and document-outline parsers.
	global $theme_vive_blog_post;
	if ( ! preg_match( '#<h1\b#i', $html ) ) {
		$heading_text = $theme_vive_blog_post instanceof WP_Post ? get_the_title( $theme_vive_blog_post ) : '';
		$queried_post = get_queried_object();
		if ( ! $heading_text && $queried_post instanceof WP_Post ) {
			$heading_text = get_the_title( $queried_post );
		}
		if ( ! $heading_text && preg_match( '#<title\b[^>]*>(?<title>.*?)</title>#is', $html, $source_title ) ) {
			$heading_text = trim( wp_strip_all_tags( html_entity_decode( $source_title['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		}
		if ( $heading_text ) {
			$heading = '<h1 class="theme-vive-accessible-title">' . esc_html( $heading_text ) . '</h1>';
			$html = preg_replace( '#(<body\b[^>]*>)#i', '$1' . $heading, $html, 1 );
		}
	}
	global $theme_vive_seo_image;
	$theme_vive_seo_image = '';
	if ( preg_match( '#<meta\b[^>]*\bproperty=["\']og:image["\'][^>]*\bcontent=["\'](?<url>[^"\']+)["\']#i', $html, $seo_image )
		|| preg_match( '#<img\b[^>]*\bsrc=["\'](?<url>(?!data:)[^"\']+)["\']#i', $html, $seo_image ) ) {
		$theme_vive_seo_image = esc_url_raw( html_entity_decode( $seo_image['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}
	// The static source contains its original SEO tags. Remove them before
	// wp_head() so Yoast is the single source of title, description, canonical,
	// Open Graph and Twitter metadata.
	$html = preg_replace( '#<title\b[^>]*>.*?</title>#is', '', $html );
	$html = preg_replace( '#<meta\b[^>]*\bname=["\'](?:description|twitter:(?:card|site|title|description|url|image))[^"\']*["\'][^>]*>#i', '', $html );
	$html = preg_replace( '#<meta\b[^>]*\bproperty=["\']og:(?:type|url|title|description|image(?::[^"\']*)?)["\'][^>]*>#i', '', $html );
	$html = preg_replace( '#<link\b[^>]*\brel=["\']canonical["\'][^>]*>#i', '', $html );
	// Remove source JSON-LD as well. Several legacy blocks were machine-
	// translated into invalid JSON; Yoast emits the canonical schema graph.
	$html = preg_replace( '#<script\b[^>]*\btype=["\']application/ld\+json["\'][^>]*>.*?</script>#is', '', $html );
	ob_start(); wp_head(); $hook = ob_get_clean();
	// Upstream bundles were authored for the global `$` alias while WordPress
	// runs jQuery in no-conflict mode. Restore the alias only for mirror pages.
	$hook .= '<script id="theme-vive-jquery-compat">window.$=window.jQuery;</script>' . "\n";
	if ( ! empty( $theme_vive_virtual_seo['title'] ) ) {
		$virtual_image = $theme_vive_seo_image ?: get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg';
		$hook .= '<link rel="canonical" href="' . esc_url( $theme_vive_virtual_seo['url'] ) . '">' . "\n";
		$hook .= '<meta property="og:type" content="website"><meta property="og:title" content="' . esc_attr( $theme_vive_virtual_seo['title'] ) . '"><meta property="og:description" content="' . esc_attr( $theme_vive_virtual_seo['description'] ) . '"><meta property="og:url" content="' . esc_url( $theme_vive_virtual_seo['url'] ) . '"><meta property="og:image" content="' . esc_url( $virtual_image ) . '">' . "\n";
		$hook .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . esc_attr( $theme_vive_virtual_seo['title'] ) . '"><meta name="twitter:description" content="' . esc_attr( $theme_vive_virtual_seo['description'] ) . '"><meta name="twitter:image" content="' . esc_url( $virtual_image ) . '">' . "\n";
	}
	if ( theme_vive_is_blog_landing_request() ) {
		$blog_url   = home_url( '/blog/' );
		$blog_title = 'Tin tức VR, XR và công nghệ VIVE | VIVE Việt Nam';
		$blog_desc  = 'Cập nhật tin tức mới nhất về thực tế ảo, XR, VIVERSE và công nghệ nhập vai.';
		$blog_image = get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-focus-vision/images/vive-focus-vision-vr-headset-kv-1920.jpg';
		$hook .= '<link rel="canonical" href="' . esc_url( $blog_url ) . '">' . "\n";
		$hook .= '<meta property="og:type" content="website"><meta property="og:title" content="' . esc_attr( $blog_title ) . '"><meta property="og:description" content="' . esc_attr( $blog_desc ) . '"><meta property="og:url" content="' . esc_url( $blog_url ) . '"><meta property="og:image" content="' . esc_url( $blog_image ) . '">' . "\n";
		$hook .= '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . esc_attr( $blog_title ) . '"><meta name="twitter:description" content="' . esc_attr( $blog_desc ) . '"><meta name="twitter:image" content="' . esc_url( $blog_image ) . '">' . "\n";
	}
	$font_tag = '<link rel="preconnect" href="https://www.viverse.com" crossorigin><link rel="preconnect" href="https://world-assets.viverse.com" crossorigin><link rel="dns-prefetch" href="https://www.viverse.com"><link rel="dns-prefetch" href="https://world-assets.viverse.com"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><meta charset="utf-8"><style id="theme-vive-hide-widgets">[data-update-block*="price"],a[href*="find-my-order"],a[id*="func14694"],.nav20-my-orders,.nav20-order-help,[data-orders],[data-order-help],sidebar-drawer:not(.sidebar--opened),sidebar-drawer,#site-menu-sidebar,[class*="miss-out"],[id*="miss-out"],[class*="dont-miss"],[id*="dont-miss"],.toast-container,.floating-notification,.floating-toast,.sales-notification,.pop-up-banner,.lead-capture,shopify-forms-embed,.shopify-forms-embed,[id*="shopify-forms"],[class*="shopify-forms"],[data-shopify-forms],.newsletter-popup-modal,.newsletter-modal,.popup-modal,[class*="popup-modal"],[id*="popup-modal"]{display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;height:0!important;width:0!important;overflow:hidden!important;}.price,.prices,.price-block,.price-box,.price-item,.price-container,.price-and-learn-more-block .price-block,.js-price,.product-price,.product-price--original,.product-price--sale,.promotion-card__price,.promotion-card__msrp,.promotion-card__sale-price,.pdp-price,.item-price,.c-price,.unit-price,.original-price,.discount-price,.pricing,.plan-price,.card-price,.spec-price,.bundle-price,.shopify-price,.money,.nav20-price,.txt-price,.price-text,.product-card__price,.product__price,.price__regular,.price__sale,.quick-buy-price,[data-price],[class*="price-block"],[class*="product-price"],[class*="item-price"],[class*="price-box"],[class*="pdp-price"],[class*="promotion-card__price"],[class*="promotion-card__msrp"],[class*="promotion-card__sale-price"],#stay-updated,.stay-updated,.section-marketo-popup-preorder,.modal-overlap-container,.modal-overlap-bg,.modal-overlap,[id*="mktoForm"],[class*="mktoForm"],[class*="section-marketo-popup"],.jqmWindow.vive-modal,.jqmOverlay,.marketo-form-pop-up-section,.marketo-form-opacity,.marketo-open-btn,#marketo-form,.marketo-form-content,[data-force-closeable],.section-marketo-popup-preorder,.nav20-cookie-notice-block,.nav20-cookie-notice-bg,.nav20-cookie-notice-content,.nav20-cookie-notice-left,.nav20-cookie-notice-right,.cookie-notice-block,#cookie-notes,.accept-all-cookie-settings,#onetrust-consent-sdk,#onetrust-banner-sdk,.optanon-alert-box-wrapper,.cookie-banner,.cookie-consent,#cookie-banner,#cookie-notice,#chatbot-root,.chatbot-root,#getsitecontrol,.getsitecontrol,.gsc-widget,.gsc-tab,.gsc-layer,[data-widget-id],[data-gsc-widget],[class*="getsitecontrol"],[id*="getsitecontrol"],[class*="socialchat"],[id*="socialchat"],[class*="chatbot"],[id*="chatbot"],.floating-widget,.sticky-widget,.newsletter-popup,.newsletter-modal,iframe[src*="getsitecontrol"],iframe[src*="socialchat"],iframe[src*="loup"],iframe[src*="marketo"],.scroll-to-top,.back-to-top,.btn-scroll-top,.scroll-top,.scroll_top,.scroll-up,.gototop,.to-top,.go-top,#scrollUp,#return-to-top,#toTop,.toTop,#back-to-top,#scroll-to-top,#backtotop,.backtotop,[class*="scroll-to-top"],[class*="back-to-top"],[class*="scroll-top"],[class*="scrolltop"],[id*="scroll-to-top"],[id*="back-to-top"],[id*="scroll-top"],[id*="scrolltop"],[class*="totop"],[id*="totop"]{display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;height:0!important;max-height:0!important;width:0!important;overflow:hidden!important;margin:0!important;padding:0!important;}.fa-icon,.fa-icon-discord,.fa-icon-tiktok,[class*="fa-icon-discord"],[class*="fa-icon-tiktok"]{font-size:0!important;line-height:0!important;color:transparent!important;display:inline-block!important;width:20px!important;height:20px!important;vertical-align:middle!important;background-size:contain!important;background-repeat:no-repeat!important;background-position:center!important;}.fa-icon-discord,[class*="fa-icon-discord"]{background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23ffffff\'%3E%3Cpath d=\'M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z\'/%3E%3C/svg%3E")!important;}.fa-icon-tiktok,[class*="fa-icon-tiktok"]{background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23ffffff\'%3E%3Cpath d=\'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z\'/%3E%3C/svg%3E")!important;}.nav20-footer-social{display:flex!important;align-items:center!important;flex-wrap:wrap!important;gap:16px 20px!important;}.nav20-footer-social-bottom{display:inline-flex!important;align-items:center!important;gap:16px 20px!important;margin:0!important;padding:0!important;}.nav20-footer-social a{display:inline-flex!important;align-items:center!important;justify-content:center!important;color:#fff!important;text-decoration:none!important;}.nav20-footer-social svg,.nav20-footer-social i{color:#fff!important;fill:#fff!important;}</style>';
	$font_tag = '<style id="theme-vive-ui-cleanup">.nav20-link-1-item--mobile,.nav20-footer-social a:has(.icon-img_steamvr-logo),.nav20-footer-social-bottom a:has(.icon-img_steamvr-logo),.jqmWindow,.ec-select-country,.ec-select-country-func,.modal-overlap-container,.modal-overlap,.jqmOverlay{display:none!important;height:0!important;max-height:0!important;overflow:hidden!important;margin:0!important;padding:0!important}img.lazy-img,.lazy-image,.lazy-image--to-reveal{visibility:visible!important;opacity:1!important}.nav20-search-content{display:inline-flex!important;align-items:center!important;justify-content:center!important;cursor:pointer!important;min-width:32px!important;height:32px!important;margin-left:12px!important;vertical-align:middle!important;visibility:visible!important;opacity:1!important}.nav20-search-content *,#search-box,.search-box{display:none!important}.nav20-search-content::after{content:""!important;display:inline-block!important;width:24px!important;height:16px!important;background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 900 600\'%3E%3Crect width=\'900\' height=\'600\' fill=\'%23da251d\'/%3E%3Cpolygon points=\'450,150 487,265 608,265 510,336 547,450 450,380 353,450 390,336 292,265 413,265\' fill=\'%23ffff00\'/%3E%3C/svg%3E")!important;background-size:cover!important;background-position:center!important;background-repeat:no-repeat!important;border-radius:3px!important;border:1px solid rgba(255,255,255,0.3)!important;box-shadow:0 1px 4px rgba(0,0,0,0.5)!important;vertical-align:middle!important;visibility:visible!important;opacity:1!important;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease!important}.nav20-search-content:hover::after{transform:scale(1.1)!important;border-color:#00b5e2!important;box-shadow:0 0 8px rgba(0,181,226,.7)!important}.section-heading__title,h2.section-heading__title,.section-heading h2,.shopify-section h2,.shopify-section h1,.shopify-section h3,[id*="shopify-section"] h2,[id*="shopify-section"] h1,[id*="shopify-section"] .section-heading__title{color:#fff!important}.section-heading__subheading,span.section-heading__subheading,.section-heading span,.shopify-section .section-heading__subheading,.shopify-section p,[id*="shopify-section"] .section-heading__subheading,[id*="shopify-section"] p{color:#94a3b8!important;opacity:1!important}</style>' . $font_tag;
	if ( false !== strpos( $html, 'vive-eagle-page' ) ) {
		// The upstream virtual try-on depends on a third-party camera experience
		// that is not available for this localized page. Remove every launcher
		// and its iframe modal from the rendered page.
		$html = preg_replace( '#<a\b[^>]*\bjs-iframe-popup-btn\b[^>]*>.*?</a>#is', '', $html );
		$html = preg_replace( '#<div\s+class="modal-iframe-overlap-container">.*?</template>\s*</div>#is', '', $html );
		$eagle_icon_url  = get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-eagle/images/icon-play-btn.svg';
		$eagle_ring_url  = get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-eagle/images/icon-play-btn-ring.svg';
		$font_tag = '<style id="theme-vive-eagle-video-controls">.vive-eagle-page .video-block .play-scroll-video-btn{background-image:url("' . esc_url( $eagle_icon_url ) . '")!important;background-color:transparent!important}.vive-eagle-page .video-block .play-scroll-video-btn:before{background-image:url("' . esc_url( $eagle_ring_url ) . '")!important}.vive-eagle-page .video-block .play-scroll-video-btn.active{background-image:radial-gradient(circle,rgba(217,217,217,.1) 0 48%,transparent 49%)!important}.vive-eagle-page .video-block .play-scroll-video-btn.active:after{content:"";position:absolute;left:14px;top:12px;width:12px;height:16px;border-left:4px solid #fff;border-right:4px solid #fff;box-sizing:border-box}</style>' . $font_tag;
		$font_tag = '<style id="theme-vive-eagle-mobile-fixes">@media(max-width:991.98px){.vive-eagle-page .section-translate-card .main-content>h2{color:#071c2c!important;text-shadow:0 1px 2px rgba(255,255,255,.7)}.vive-eagle-page section.section-full-video.section-full-video--1190>.image-block.video-block{height:auto!important;top:0;bottom:0}}</style>' . $font_tag;
	}
	if ( 'shop-us.vive.com' === $source_host ) {
		$font_tag = '<style id="theme-vive-shop-contrast">.main-content [id*="shopify-section"] .section-heading__title,.main-content .product__title{color:#182431!important}.main-content .card--no-background .card__text>h3,.main-content .card--no-background .card__text>h3 span,.main-content .card--no-background .card__text>span{color:#fff!important}.main-content .section-heading .button .button__text,.main-content .section-heading .button .button__icon{color:#fff!important}.main-content .section-heading .button svg path{fill:#fff!important}.announcement-bar .announcement>a{display:inline-block;transform:translateY(2px)}.facets-sidebar,aside.facets-sidebar,facet-filters-form,#site-filters-sidebar,.collection-heading__actions,.sorting__prewrapper{display:none!important;visibility:hidden!important;height:0!important;width:0!important;overflow:hidden!important;margin:0!important;padding:0!important;pointer-events:none!important;}#CollectionProductGrid,#main-collection-product-grid{grid-column:1/-1!important;width:100%!important;margin:0!important;padding:0!important;display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:24px!important;}.collection-heading-container{grid-column:1/-1!important;width:100%!important;margin:0 0 20px 0!important;display:flex!important;justify-content:flex-start!important;align-items:center!important;}@media screen and (max-width:1199px){#CollectionProductGrid,#main-collection-product-grid{grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:20px!important;}}@media screen and (max-width:767px){#CollectionProductGrid,#main-collection-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:16px!important;}}@media screen and (max-width:480px){#CollectionProductGrid,#main-collection-product-grid{grid-template-columns:1fr!important;gap:16px!important;}}#main-collection-product-grid .product-item{display:flex!important;flex-direction:column!important;height:100%!important;}sidebar-drawer:not(.sidebar--opened){display:none!important;}</style>' . $font_tag;
	}
	if ( preg_match( '#/(?:pages/us|footer-sites/[^/]+)/product/index\.html?$#i', str_replace( '\\', '/', (string) $file ) ) || preg_match( '#/product/index\.html?$#i', str_replace( '\\', '/', (string) $file ) ) ) {
		$font_tag = '<style id="theme-vive-product-listing-page">
.product-list-page .product-item .product-price { display: block !important; visibility: visible !important; opacity: 1 !important; height: auto !important; max-height: none !important; margin: 16px 0 0 !important; }
.product-list-page .productGroups { max-width: 1140px !important; margin: 0 auto !important; padding: 0 20px !important; }
.product-list-page .productGroup { border-bottom: 1px solid #e5e7eb !important; padding: 50px 0 !important; }
.product-list-page .productGroup:last-of-type { border-bottom: none !important; }
.product-list-page .productGroupProductTag { display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 48px !important; min-height: 380px !important; padding: 0 !important; box-sizing: border-box !important; }
.product-list-page .productGroup.productGroup__odd .productGroupProductTag { flex-direction: row !important; }
.product-list-page .productGroup.productGroup__even .productGroupProductTag { flex-direction: row-reverse !important; }
.product-list-page .productGroupProductTag .flex-item:not(.flex-item__image) { flex: 0 1 440px !important; max-width: 440px !important; width: 100% !important; order: 0 !important; }
.product-list-page .productGroupProductTag .text-category-title { font-size: 28px !important; font-weight: 700 !important; color: #111827 !important; margin-bottom: 6px !important; line-height: 1.2 !important; }
.product-list-page .productGroupProductTag .text-sub { font-size: 13.5px !important; font-weight: 500 !important; color: #4b5563 !important; margin-bottom: 16px !important; line-height: 1.4 !important; }
.product-list-page .productGroupProductTag .text-des { font-size: 13px !important; line-height: 1.6 !important; color: #4b5563 !important; margin-bottom: 16px !important; }
.product-list-page .productGroupProductTag .flex-item.flex-item__image { flex: 1 1 520px !important; display: flex !important; align-items: center !important; justify-content: center !important; order: 0 !important; }
.product-list-page .productGroupProductTag .flex-item.flex-item__image img { display: block !important; max-width: 100% !important; max-height: 400px !important; width: auto !important; height: auto !important; object-fit: contain !important; transition: transform 0.3s ease !important; }
.product-list-page .productGroupProductTag .flex-item.flex-item__image:hover img { transform: scale(1.02) !important; }
.product-list-page .theme-vive-listing-item-actions { display: flex !important; flex-direction: column !important; align-items: flex-start !important; gap: 12px !important; margin-top: 16px !important; }
.product-list-page .theme-vive-listing-price { font-size: 15px !important; font-weight: 600 !important; color: #111827 !important; margin-bottom: 2px !important; }
.product-list-page .theme-vive-buy-button { display: inline-flex !important; align-items: center !important; justify-content: center !important; background-color: #2196f3 !important; color: #ffffff !important; font-weight: 600 !important; font-size: 14px !important; line-height: 1 !important; padding: 9px 36px !important; border-radius: 999px !important; text-decoration: none !important; border: none !important; transition: background-color 0.2s ease, transform 0.2s ease !important; cursor: pointer !important; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.25) !important; }
.product-list-page .theme-vive-buy-button:hover { background-color: #1976d2 !important; color: #ffffff !important; transform: translateY(-1px) !important; }
.product-list-page .theme-vive-detail-button { display: inline-flex !important; align-items: center !important; color: #00b5e2 !important; font-weight: 600 !important; font-size: 14px !important; text-decoration: none !important; transition: color 0.2s ease !important; }
.product-list-page .theme-vive-detail-button:hover { color: #008cb0 !important; text-decoration: underline !important; }
.product-list-page .product-list.product-list-total-item-2 { display: flex !important; justify-content: center !important; gap: 80px !important; margin-top: 36px !important; }
.product-list-page .product-list.product-list-total-item-2 .product-item { flex: 0 1 260px !important; text-align: center !important; display: flex !important; flex-direction: column !important; align-items: center !important; }
.product-list-page .product-list.product-list-total-item-2 .product-image img { max-height: 140px !important; margin: 0 auto 12px !important; display: block !important; object-fit: contain !important; }
.product-list-page .product-list.product-list-total-item-2 .product-name-title { font-size: 16px !important; font-weight: 600 !important; color: #111827 !important; margin-bottom: 6px !important; }
.product-list-page .product-list.product-list-total-item-2 .product-name-text { font-size: 12px !important; color: #6b7280 !important; line-height: 1.4 !important; max-width: 240px !important; margin: 0 auto !important; }
.product-list-page .product-list.product-list-total-item-2 .theme-vive-listing-item-actions { align-items: center !important; margin-top: 14px !important; }
@media (min-width: 768px) { .product-list-page .productGroupProductTag .m-img { display: none !important; } }
@media (max-width: 767.98px) {
	.product-list-page .productGroupProductTag { flex-direction: column !important; min-height: auto !important; padding: 20px 0 !important; }
	.product-list-page .productGroupProductTag .flex-item:not(.flex-item__image) { max-width: 100% !important; }
	.product-list-page .productGroupProductTag .flex-item.flex-item__image { display: none !important; }
	.product-list-page .productGroupProductTag .m-img { display: block !important; width: 100% !important; max-width: 360px !important; margin: 18px auto !important; object-fit: contain !important; }
	.product-list-page .product-list.product-list-total-item-2 { flex-direction: column !important; gap: 30px !important; }
}
</style>' . $font_tag;
	}
	$html = preg_replace( '#</head>#i', $font_tag . $hook . '</head>', $html, 1 );
	// WordPress can emit an invalid escaped question mark in its generated
	// speculation-rules JSON on mirrored URLs. Prefetching is non-essential here;
	// remove the malformed block so it cannot raise a page-level JS error.
	$html = preg_replace( '#<script\b[^>]*\btype=["\']speculationrules["\'][^>]*>.*?</script>#is', '', $html );
	ob_start(); wp_body_open(); $hook = ob_get_clean(); $html = preg_replace( '#(<body\b[^>]*>)#i', '$1' . $hook, $html, 1 );
	$guard = '<script id="theme-vive-link-guard">
(function(){
	function local(u){
		if(!u||u==="#"||u.charAt(0)==="#"||u.indexOf("javascript:")===0||u.indexOf("tel:")===0||u.indexOf("mailto:")===0){
			return true;
		}
		if(u.charAt(0)==="/"&&u.charAt(1)!=="/"){
			return true;
		}
		try{
			var x=new URL(u,location.href);
			return x.origin===location.origin||x.hostname===location.hostname||x.hostname.indexOf("vive.com")!==-1||x.hostname.indexOf("viverse.com")!==-1||x.hostname.indexOf("viveport.com")!==-1||x.hostname.indexOf("zalo.me")!==-1||x.hostname.indexOf("google.com")!==-1||x.hostname.indexOf("goo.gl")!==-1||x.hostname.indexOf("facebook.com")!==-1||x.hostname.indexOf("youtube.com")!==-1||x.hostname.indexOf("instagram.com")!==-1||x.hostname.indexOf("tiktok.com")!==-1||x.hostname.indexOf("discord.gg")!==-1||x.hostname.indexOf("discord.com")!==-1;
		}catch(e){return true;}
	}
	document.addEventListener("click",function(e){
		var videoButton=e.target.closest&&e.target.closest(".play-modal-video-btn[data-vid]");
		if(videoButton){
			var videoId=(videoButton.getAttribute("data-vid")||"").trim();
			if(/^[A-Za-z0-9_-]{6,20}$/.test(videoId)){
				e.preventDefault();
				e.stopImmediatePropagation();
				openThemeViveVideo(videoId);
				return;
			}
		}
		var videoClose=e.target.closest&&e.target.closest(".theme-vive-video-close,.close-animatedModal");
		if(videoClose){
			e.preventDefault();
			closeThemeViveVideo();
			return;
		}
		var activeVideoModal=document.querySelector(".theme-vive-video-open");
		if(activeVideoModal&&e.target===activeVideoModal){closeThemeViveVideo();return;}
		var a=e.target.closest&&e.target.closest("a[href]");
		if(a){
			var href=a.getAttribute("href");
			var target=a.getAttribute("target");
			if(target==="_blank"||a.closest(".vive-floating-widget")||a.closest(".vive-support-section")||a.closest(".nav20-footer-social")){
				return;
			}
			// Blog cards may be recreated after page load from cached/API data.
			// Always keep their full legacy path (including date folders) local.
			try{
				var blogUrl=new URL(href,location.href);
				if(/^blog\.vive\.com$/i.test(blogUrl.hostname)){
					e.preventDefault();
					var blogPath=blogUrl.pathname.replace(/^\/us(?:\/|$)/i,"/");
					location.href=' . wp_json_encode( rtrim( home_url( '/blog/' ), '/' ) ) . '+"/"+blogPath.replace(/^\/+/,"")+blogUrl.search+blogUrl.hash;
					return;
				}
			}catch(ignore){}
			if(href&&!local(href)){
				e.preventDefault();
				location.href=' . wp_json_encode( home_url( '/' ) ) . ';
			}
		}
	},true);
	function getThemeViveVideoModal(){
		var modal=document.getElementById("animatedModal")||document.getElementById("themeViveVideoModal");
		if(!modal){
			modal=document.createElement("div");
			modal.id="themeViveVideoModal";
			modal.innerHTML="<button class=\"theme-vive-video-close\" type=\"button\" aria-label=\"Đóng video\">&times;</button><div class=\"modal-content\"></div>";
			document.body.appendChild(modal);
		}
		return modal;
	}
	function openThemeViveVideo(videoId){
		var modal=getThemeViveVideoModal();
		var content=modal.querySelector(".modal-content");
		if(!modal.querySelector(".theme-vive-video-close")&&!modal.querySelector(".close-animatedModal")){
			var close=document.createElement("button");
			close.type="button";close.className="theme-vive-video-close";close.setAttribute("aria-label","Đóng video");close.innerHTML="&times;";modal.insertBefore(close,modal.firstChild);
		}
		if(content){content.innerHTML="<div class=\"theme-vive-video-frame\"><iframe src=\"https://www.youtube-nocookie.com/embed/"+encodeURIComponent(videoId)+"?autoplay=1&amp;rel=0\" title=\"Video VIVE\" allow=\"autoplay; encrypted-media; picture-in-picture\" allowfullscreen></iframe></div>";}
		modal.classList.remove("animatedModal-off");
		modal.classList.add("animatedModal-on","theme-vive-video-open");
		modal.style.display="flex";modal.style.opacity="1";modal.style.zIndex="999999";
		document.body.classList.add("theme-vive-video-modal-open");
	}
	function closeThemeViveVideo(){
		var modal=document.querySelector(".theme-vive-video-open");
		if(!modal)return;
		var content=modal.querySelector(".modal-content");if(content)content.innerHTML="";
		modal.classList.remove("animatedModal-on","theme-vive-video-open");modal.classList.add("animatedModal-off");
		modal.style.display="none";modal.style.opacity="0";modal.style.zIndex="-9999";
		document.body.classList.remove("theme-vive-video-modal-open");
	}
	document.addEventListener("keydown",function(e){if(e.key==="Escape")closeThemeViveVideo();});
	function toggleEagleFaq(toggle){
		var item=toggle.closest(".faq-item");
		var content=item&&item.querySelector(".faq-item-content");
		if(!content)return;
		var open=toggle.getAttribute("aria-expanded")!=="true";
		document.querySelectorAll(".vive-eagle-page .section-faq .faq-item-title[aria-expanded=true]").forEach(function(other){
			if(other===toggle)return;
			other.setAttribute("aria-expanded","false");
			other.classList.remove("active");
			var otherContent=other.closest(".faq-item").querySelector(".faq-item-content");
			if(otherContent)otherContent.style.maxHeight="0px";
		});
		toggle.setAttribute("aria-expanded",open?"true":"false");
		toggle.classList.toggle("active",open);
		content.style.maxHeight=open?content.scrollHeight+"px":"0px";
	}
	document.addEventListener("click",function(e){
		var toggle=e.target.closest&&e.target.closest(".vive-eagle-page .section-faq .faq-item-title");
		if(!toggle)return;
		e.preventDefault();e.stopImmediatePropagation();toggleEagleFaq(toggle);
	},true);
	document.addEventListener("keydown",function(e){
		if(e.key!=="Enter"&&e.key!==" ")return;
		var toggle=e.target.closest&&e.target.closest(".vive-eagle-page .section-faq .faq-item-title");
		if(!toggle)return;
		e.preventDefault();e.stopImmediatePropagation();toggleEagleFaq(toggle);
	},true);
	function startFlowHeroVideo(){
		if(!window.matchMedia("(max-width: 767px)").matches)return;
		var video=document.querySelector(".section-kv .new-video-bg > video.show-desktop[title=\"VIVE Flow\"]");
		if(!video)return;
		var localVideo=' . wp_json_encode( get_template_directory_uri() . '/assets/media/filer_public/fed-assets/vive-flow-2022/images/video/vive-flow-banner-1920.mp4' ) . ';
		if(video.getAttribute("src")!==localVideo){video.innerHTML="";video.setAttribute("src",localVideo);}
		video.muted=true;video.defaultMuted=true;video.loop=true;video.autoplay=true;video.playsInline=true;
		video.setAttribute("muted","");video.setAttribute("autoplay","");video.setAttribute("playsinline","");
		try{video.load();var playing=video.play();if(playing&&playing.catch)playing.catch(function(){});}catch(ignore){}
	}
	document.addEventListener("DOMContentLoaded",startFlowHeroVideo);
	window.addEventListener("load",startFlowHeroVideo);
	document.addEventListener("submit",function(e){
		var f=e.target;
		if(f.action&&!local(f.action)){
			e.preventDefault();
			location.href=' . wp_json_encode( home_url( '/' ) ) . ';
		}
	},true);
	function cleanTopButtons(){
		var sel = ".scroll-to-top, .back-to-top, .btn-scroll-top, .scroll-top, .scroll_top, .scroll-up, .gototop, .to-top, .go-top, #scrollUp, #return-to-top, #toTop, .toTop, #back-to-top, #scroll-to-top, #backtotop, .backtotop, .btn-to-top, .js-to-top, .js-scroll-top";
		document.querySelectorAll(sel).forEach(function(b){
			Array.from(b.childNodes).forEach(function(n){if(n.nodeType===3){n.nodeValue="";}});
		});
	}
	var isCleaning = false;
	function killWidgets(){
		if(isCleaning) return;
		isCleaning = true;
		var sel = "sidebar-drawer:not(.sidebar--opened), sidebar-drawer, #site-menu-sidebar, .toast-container, .floating-notification, .floating-toast, .sales-notification, .pop-up-banner, .lead-capture, shopify-forms-embed, .shopify-forms-embed, [id*=\"shopify-forms\"], [class*=\"shopify-forms\"], [data-shopify-forms], .newsletter-popup-modal, .newsletter-modal, .popup-modal, [class*=\"popup-modal\"], [id*=\"popup-modal\"], .price, .prices, .price-block, .price-box, .price-item, .price-container, .js-price, .product-price, .promotion-card__price, .pdp-price, .item-price, .c-price, .unit-price, .original-price, .discount-price, .pricing, .plan-price, .card-price, .spec-price, .bundle-price, .shopify-price, .money, .nav20-price, .txt-price, .price-text, .product-card__price, .product__price, .quick-buy-price, [data-price], #stay-updated, .stay-updated, .section-marketo-popup-preorder, .modal-overlap-container, .modal-overlap-bg, .modal-overlap, .jqmWindow.vive-modal, .jqmOverlay, .marketo-form-pop-up-section, .marketo-form-opacity, .marketo-open-btn, #marketo-form, .marketo-form-content, [data-force-closeable], .nav20-cookie-notice-block, .nav20-cookie-notice-bg, .nav20-cookie-notice-content, .cookie-notice-block, #cookie-notes, #onetrust-consent-sdk, .optanon-alert-box-wrapper, .cookie-banner, .cookie-consent, #cookie-banner, #cookie-notice, #chatbot-root, .chatbot-root, #getsitecontrol, .getsitecontrol, .gsc-widget, .gsc-tab, .gsc-layer, [data-widget-id], [data-gsc-widget], iframe[src*=\"getsitecontrol\"], iframe[src*=\"socialchat\"], iframe[src*=\"loup\"], iframe[src*=\"marketo\"]";
		try{
			document.querySelectorAll(sel).forEach(function(e){e.remove();});
			cleanTopButtons();
		}catch(e){}
		isCleaning = false;
	}
	var debounceTimer = null;
	function scheduleCleanup(){
		if(debounceTimer) return;
		debounceTimer = setTimeout(function(){
			debounceTimer = null;
			killWidgets();
		}, 300);
	}
	try{
		var obs=new MutationObserver(function(){scheduleCleanup();});
		obs.observe(document.documentElement,{childList:true,subtree:true});
	}catch(e){}
	document.addEventListener("DOMContentLoaded",killWidgets);
	window.addEventListener("load",killWidgets);
})();
</script>';
	// Insert Customer Support section right above the footer elements
	$support_html = theme_vive_customer_support_html();
	if ( false !== strpos( $html, '<div class="nav20-main-footer">' ) ) {
		$html = str_replace( '<div class="nav20-main-footer">', $support_html . '<div class="nav20-main-footer">', $html );
	} elseif ( false !== strpos( $html, '<div class="nav20-footer-top-content">' ) ) {
		$html = str_replace( '<div class="nav20-footer-top-content">', $support_html . '<div class="nav20-footer-top-content">', $html );
	} elseif ( preg_match( '#<footer\b[^>]*>#i', $html ) ) {
		$html = preg_replace( '#(<footer\b[^>]*>)#i', $support_html . '$1', $html, 1 );
	} elseif ( false !== strpos( $html, '<div class="footer"' ) || false !== strpos( $html, '<div class="footer ' ) ) {
		$html = preg_replace( '#(<div\b[^>]*\bclass=["\'][^"\']*\bfooter\b[^"\']*["\'][^>]*>)#i', $support_html . '$1', $html, 1 );
	} else {
		$html = preg_replace( '#(</body>)#i', $support_html . '$1', $html, 1 );
	}
	ob_start(); wp_footer(); $hook = ob_get_clean();
	$html = preg_replace( '#</body>#i', $guard . $hook . '</body>', $html, 1 );
	// The speculative-loading block may also be registered for wp_footer().
	$html = preg_replace( '#<script\b[^>]*\btype=["\']speculationrules["\'][^>]*>.*?</script>#is', '', $html );
	if ( $is_shop_mirror ) {
		// These Shopify services provide analytics, consent UI, account and cart
		// synchronization for the remote store. Collection browsing and filters
		// use the mirrored local assets and do not depend on them.
		$html = preg_replace(
			'#<script\b[^>]*\bsrc=["\'](?:https?:)?//(?:shop-us\.vive\.com/cdn/(?:wpm|shopifycloud/(?:privacy-banner|perf-kit|shop-js|portable-wallets|storefront/))/|cdn\.shopify\.com/(?:shopifycloud/shop-js|storefront/))[^"\']*["\'][^>]*>\s*</script>#is',
			'',
			$html
		);
		// WordPress hooks above may print commerce integrations after the first
		// cleanup pass, so apply the same non-visual cleanup to the final document.
		$html = preg_replace_callback( '#<script\b[^>]*>.*?</script>#is', static function ( $match ) {
			return preg_match( '#(?:shopify\.event_observer|ShopifyAnalytics|__TREKKIE|wpmLoader|ShopifyPay|shopify\.dynamic_checkout|portableWallets|init-shop-cart-sync|Shopify\.featureAssets|shop\.app/pay|cart-shipping-calculator-pro)#i', $match[0] ) ? '' : $match[0];
		}, (string) $html );
		$html = preg_replace( '#<link\b[^>]*(?:shop\.app|shopify-accelerated-checkout|portable-wallets)[^>]*>#i', '', $html );
		$html = preg_replace( '#<style\b[^>]*\bid=["\']shopify-accelerated-checkout[^"\']*["\'][^>]*>.*?</style>#is', '', $html );
	}
	// A few plugins and legacy templates add anchors during wp_head/wp_footer,
	// after the main href rewrite above. Normalize those late links as well.
	return preg_replace_callback( '#(<a\b[^>]*?\bhref\s*=\s*["\'])(?<url>[^"\']+)(["\'])#i', static function ( $match ) {
		$parts = wp_parse_url( html_entity_decode( $match['url'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$host  = strtolower( (string) ( $parts['host'] ?? '' ) );
		$local = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
		if ( $host && $host !== $local ) { return $match[0]; }
		$path = (string) ( $parts['path'] ?? '' );
		if ( preg_match( '#^/support/[^/]+/.+#i', $path ) || preg_match( '#^/(?:contact/hc/en-us|download|location-based-experiences|vive-desk|vive-streaming)/?$#i', $path ) ) {
			return $match[1] . esc_url( home_url( '/support/' ) ) . $match[3];
		}
		if ( preg_match( '#^/(?:roundtable|click-detail(?:-bestvrdeals)?)/?$#i', $path ) ) {
			return $match[1] . esc_url( home_url( '/' ) ) . $match[3];
		}
		if ( preg_match( '#^/product/(?:overview|specs?)(?:-[0-9]+)?/?$#i', $path ) ) {
			return $match[1] . esc_url( home_url( '/product/' ) ) . $match[3];
		}
		if ( preg_match( '#^/(?:(?:19|20)[0-9]{2}/[0-9]{2}|category/[^/]+(?:/[^/]+)*|tag/[^/]+(?:/[^/]+)*)/?$#i', $path ) ) {
			return $match[1] . esc_url( home_url( '/blog/' ) ) . $match[3];
		}
		return $match[0];
	}, $html );
}

/** Keep the localized mirror UI working when full rendering falls back. */
function theme_vive_prepare_fallback_html( $html, $file ) {
	if ( 'blog.vive.com' === theme_vive_source_host( $file ) || theme_vive_is_blog_request() || 'shop-us.vive.com' === theme_vive_source_host( $file ) || 'shop' === theme_vive_source_host( $file ) ) {
		$html = theme_vive_use_homepage_chrome( $html );
		if ( 'blog.vive.com' === theme_vive_source_host( $file ) || theme_vive_is_blog_request() ) {
			try {
				$html = theme_vive_blog_prepare_html( $html, $file );
			} catch ( Throwable $error ) {
				error_log( 'theme_vive_blog fallback data failed: ' . $error->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}
		}
	}
	if ( function_exists( 'theme_vive_products_prepare_html' ) ) {
		try {
			$html = theme_vive_products_prepare_html( $html, $file );
		} catch ( Throwable $error ) {
			error_log( 'theme_vive_products fallback data failed: ' . $error->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}
	$html = theme_vive_remove_header_cart( $html );
	$html = preg_replace_callback(
		'#(?:(?:https?:)?//(?<host>(?:[^/]+\.)?vive\.com))?(?<path>/(?:static|media|express_static|express_media)/[^"\'\s<>\),;]+)#i',
		static function ( $match ) {
			// Fallback is only used for bundled mirror snapshots. Point their known
			// asset roots at the bundled directory unconditionally; checking the
			// filesystem here produced false negatives on the production mount.
			return get_template_directory_uri() . '/assets' . $match['path'];
		},
		(string) $html
	);
	// Blog card data is JSON and may escape every slash, so the ordinary URL
	// rewrite above cannot see those image paths.
	$html = preg_replace_callback( '#(?<path>\\\\/(?:static|media|express_static|express_media)(?:\\\\/[^"\']+)+)#i', static function ( $match ) {
		$path = str_replace( '\\/', '/', $match['path'] );
		return get_template_directory_uri() . '/assets' . $path;
	}, $html );

	// Use the compact curated map without the expensive script-protection regex
	// that triggered fallback on resource-constrained production workers.
	$translations = theme_vive_translation_map( false );
	$html = preg_replace_callback( '#>(?<text>[^<>]+)<#s', static function ( $match ) use ( $translations ) {
		$key = trim( html_entity_decode( $match['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		if ( ! isset( $translations[ $key ] ) || '' === $translations[ $key ] ) { return $match[0]; }
		$left = substr( $match['text'], 0, strlen( $match['text'] ) - strlen( ltrim( $match['text'] ) ) );
		$right = substr( $match['text'], strlen( rtrim( $match['text'] ) ) );
		return '>' . $left . esc_html( $translations[ $key ] ) . $right . '<';
	}, $html );
	$html = preg_replace( '#\bdata-srcset\s*=#i', 'srcset=', $html );
	$html = preg_replace( '#\bdata-src\s*=#i', 'src=', $html );
	$html = preg_replace_callback( '#<html\b(?<attrs>[^>]*)>#i', static function ( $match ) {
		$attrs = preg_replace( '#\s+(?:lang|dir)\s*=\s*(?:["\'][^"\']*["\']|[^\s>]+)#i', '', $match['attrs'] );
		return '<html' . $attrs . ' lang="vi" dir="ltr">';
	}, $html, 1 );
	$theme_css = add_query_arg( 'ver', '3.3.14', get_stylesheet_uri() );
	$head = '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
		. '<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">'
		. '<link rel="stylesheet" href="' . esc_url( $theme_css ) . '">'
		. '<style id="theme-vive-fallback-ui">html,body,button,input,select,textarea{font-family:"Be Vietnam Pro",Arial,sans-serif!important}.nav20-cookie-notice-block,.nav20-cookie-notice-bg,.cookie-banner,#cookie-notice,.icon-img_steamvr-logo,a:has(.icon-img_steamvr-logo){display:none!important}</style>';
	$html = preg_replace( '#</head>#i', $head . '</head>', $html, 1 );
	if ( false === strpos( $html, 'vive-support-section' ) ) {
		$support = theme_vive_customer_support_html();
		if ( false !== strpos( $html, '<div class="nav20-main-footer">' ) ) {
			$html = str_replace( '<div class="nav20-main-footer">', $support . '<div class="nav20-main-footer">', $html );
		} else {
			$html = preg_replace( '#<footer\b#i', $support . '<footer', $html, 1 );
		}
	}
	// Fallback pages bypass the full renderer's anchor pass. Normalize every
	// header/content link here as well so /us and retired external VIVE hosts do
	// not leak into production when a blog page falls back.
	$html = preg_replace_callback( '#(<a\b[^>]*?\bhref\s*=\s*["\'])(?<url>[^"\']+)(["\'])#i', static function ( $match ) {
		if ( false !== strpos( $match['url'], '${' ) || false !== strpos( $match['url'], '{{' ) ) { return $match[0]; }
		return $match[1] . esc_url( theme_vive_local_link( $match['url'] ) ) . $match[3];
	}, $html );
	return $html;
}

/**
 * Send a mirrored page without allowing one plugin/rendering error to produce
 * a misleading HTTP 200 response with an empty body.
 */
function theme_vive_send_page( $file ) {
	$html = '';
	$mode = 'rendered';
	$error_class = '';
	$buffer_level = ob_get_level();
	try {
		$html = theme_vive_render( $file );
	} catch ( Throwable $error ) {
		while ( ob_get_level() > $buffer_level ) { ob_end_clean(); }
		$mode = 'fallback';
		$error_class = get_class( $error );
		error_log( 'theme_vive_render failed for ' . basename( (string) $file ) . ': ' . $error->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		$mode = 'fallback';
		$html = is_readable( $file ) ? file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
		$html = theme_vive_prepare_fallback_html( $html, $file );
	}
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		status_header( 500 );
		$html = '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>VIVE Việt Nam</title></head><body><main><h1>Trang tạm thời chưa khả dụng</h1><p>Vui lòng thử lại sau.</p></main></body></html>';
	} else {
		status_header( 200 );
	}
	header( 'Content-Type: text/html; charset=UTF-8' );
	header( 'X-Theme-Vive-Build: 20260907-product-fix-5' );
	header( 'X-Theme-Vive-Render: ' . $mode );
	if ( $error_class ) { header( 'X-Theme-Vive-Error-Class: ' . sanitize_key( $error_class ) ); }
	nocache_headers();
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}

function theme_vive_router() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
	// Redirect native WooCommerce single product to the new shop product detail page
	if ( is_singular( 'product' ) ) {
		global $post;
		$slug = $post ? $post->post_name : '';
		if ( $slug ) {
			wp_safe_redirect( home_url( '/shop/products/' . $slug . '/' ), 301 );
			exit;
		}
	}
	if ( function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() || is_account_page() ) ) { return; }
	if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
		wp_safe_redirect( home_url( '/shop/collections/' ), 301 );
		exit;
	}
	$queried_id = (int) get_queried_object_id();
	$woo_pages  = array_filter( array_map( 'absint', array(
		get_option( 'woocommerce_shop_page_id' ),
		get_option( 'woocommerce_cart_page_id' ),
		get_option( 'woocommerce_checkout_page_id' ),
		get_option( 'woocommerce_myaccount_page_id' ),
	) ) );
	if ( $queried_id && in_array( $queried_id, $woo_pages, true ) ) { return; }
	$request = theme_vive_safe_path( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	// These virtual files are generated by WordPress and Yoast SEO. Returning
	// here prevents the mirror fallback from replacing them with an HTML page.
	if ( '/robots.txt' === untrailingslashit( strtolower( (string) $request ) ) || preg_match( '#/(?:sitemap(?:_index)?|[^/]+-sitemap(?:[0-9]+)?)\.xml$#i', (string) $request ) ) { return; }
	if ( preg_match( '#^/(?:checkout|cart|my-account)(?:/|$)#i', $request ) ) { return; }
	if ( preg_match( '#^/(?:wp-admin|wp-includes|wp-content|wp-json|xmlrpc\.php)/#i', $request ) ) { return; }
	if ( preg_match( '#^/(?:rest|redirect|media|static|express_static|express_media)/#i', $request ) || preg_match( '#\.(?:png|jpg|jpeg|gif|webp|svg|ico|css|js|woff|woff2|ttf|mp4|webm|map)$#i', $request ) ) {
		status_header( 404 );
		exit;
	}
	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home_path && '/' !== $home_path && 0 === strpos( $request, $home_path ) ) {
		$request = substr( $request, strlen( rtrim( $home_path, '/' ) ) );
		if ( '' === $request ) { $request = '/'; }
	}
	// Deep pages from the retired support snapshot are extremely expensive and
	// can exhaust the local/production PHP worker. Keep old bookmarks useful by
	// consolidating them at the working support landing page.
	if ( preg_match( '#^/support/[^/]+/.+#i', $request ) ) {
		wp_safe_redirect( home_url( '/support/' ), 301 );
		exit;
	}

	// Xử lý nhanh các Wagtail Blog API còn lại mà không gọi cURL ra ngoài
	if ( 0 === strpos( $request, '/api/v2/' ) ) {
		header( 'Content-Type: application/json; charset=UTF-8' );
		header( 'Access-Control-Allow-Origin: *' );
		status_header( 200 );
		echo wp_json_encode( array( 'meta' => array( 'total_count' => 0 ), 'items' => array() ) );
		exit;
	}
	
	// 1. Tự động chuyển hướng (301 Redirect) từ URL cũ /vive/www/ hoặc /us/ sang URL sạch
	if ( preg_match( '#^/vive/www(?<path>/.*)?$#i', $request, $m ) ) {
		$clean = ! empty( $m['path'] ) ? theme_vive_clean_slug_path( $m['path'] ) : '/';
		$target = '/' === $clean ? home_url( '/' ) : home_url( $clean );
		$query = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_safe_redirect( $target . $query, 301 );
		exit;
	}
	if ( preg_match( '#^/vive/shop(?:-us)?(?<path>/.*)?$#i', $request, $m ) ) {
		$clean = ! empty( $m['path'] ) ? theme_vive_clean_slug_path( $m['path'] ) : '/';
		$target = home_url( '/shop' . ( '/' === $clean ? '/' : $clean ) );
		$query = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_safe_redirect( $target . $query, 301 );
		exit;
	}
	if ( preg_match( '#^/vive/(?<slug>business|developer|service|blog|mars|sync|viverse)(?<path>/.*)?$#i', $request, $m ) ) {
		$clean = ! empty( $m['path'] ) ? theme_vive_clean_slug_path( $m['path'] ) : '/';
		$target = home_url( '/' . strtolower( $m['slug'] ) . ( '/' === $clean ? '/' : $clean ) );
		$query = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_safe_redirect( $target . $query, 301 );
		exit;
	}
	if ( preg_match( '#^/us(?<path>/.*)?$#i', $request, $m ) ) {
		$clean = ! empty( $m['path'] ) ? theme_vive_clean_slug_path( $m['path'] ) : '/';
		$target = '/' === $clean ? home_url( '/' ) : home_url( $clean );
		$query = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_safe_redirect( $target . $query, 301 );
		exit;
	}
	
	// 2. Chuyển hướng các trang collections và products của Shopify sang URL shop tương ứng
	if ( preg_match( '#^/collections/[^/]+/products/(?<slug>[^/]+)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/shop/products/' . $m['slug'] . '/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/shop/collections/[^/]+/products/(?<slug>[^/]+)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/shop/products/' . $m['slug'] . '/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/collections(?:/all)?/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/shop/collections/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/collections/(?<subslug>[^/]+)/?$#i', $request, $m ) ) {
		$sub = $m['subslug'];
		wp_safe_redirect( home_url( '/shop/collections/' . $sub . '/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/products/(?<slug>[^/]+)/?$#i', $request, $m ) ) {
		$slug = $m['slug'];
		wp_safe_redirect( home_url( '/shop/products/' . $slug . '/' ), 301 );
		exit;
	}

	// Chuyển hướng bỏ trang "Đơn hàng của tôi" (Find my order)
	if ( preg_match( '#^/(?:shop/)?pages/find-my-order/?$#i', $request ) || preg_match( '#^/(?:find-my-order|my-order|my-orders|order|orders)/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	// Chuyển hướng các URL có tiền tố ngôn ngữ cũ (/tw/, /us/, /en/, /cn/) sang URL gốc
	if ( preg_match( '#^/(?:tw|us|en|cn)(?<subpath>/.*)?$#i', $request, $m ) ) {
		$target = ! empty( $m['subpath'] ) ? $m['subpath'] : '/';
		wp_safe_redirect( home_url( $target ), 301 );
		exit;
	}

	// Chuyển hướng các URL tin tức dạng ngày /YYYY-MM-DD/ hoặc /YYYY/ sang /newsroom/YYYY-MM-DD/
	if ( preg_match( '#^/(?<date>(?:19|20)[0-9]{2}(?:-[0-9]{2})?(?:-[0-9]{2})?(?:-[0-9]+)?)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/newsroom/' . $m['date'] . '/' ), 301 );
		exit;
	}

	// Chuyển hướng các route cha sang trang con chính
	if ( preg_match( '#^/newsroom/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/newsroom/2025/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/solution/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/business/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/ready/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/vive-ready/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/rental/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/business/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/vive-hub/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/vive-hub/download/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/product/(?<prod>vive-focus-vision|vive-pro2|vive-pro2-full-kit|vive-flow|vive-xr-elite|vive-eagle)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/product/' . strtolower( $m['prod'] ) . '/overview/' ), 301 );
		exit;
	}

	// Chuyển hướng URL WooCommerce cũ (/san-pham/, /product/, /danh-muc-san-pham/) sang trang shop tương ứng
	if ( preg_match( '#^/(?:san-pham|product)/(?<slug>[^/]+)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/shop/products/' . $m['slug'] . '/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/(?:danh-muc-san-pham|danh-muc)/(?<slug>[^/]+)/?$#i', $request, $m ) ) {
		wp_safe_redirect( home_url( '/shop/collections/' . $m['slug'] . '/' ), 301 );
		exit;
	}
	if ( preg_match( '#^/(?:san-pham|cua-hang|danh-muc-san-pham|danh-muc)/?$#i', $request ) ) {
		wp_safe_redirect( home_url( '/shop/collections/' ), 301 );
		exit;
	}

	// 3. Xử lý hiển thị trang chủ
	if ( '/' === $request ) {
		$file = theme_vive_page_file( 'vive.com', '/us/' );
		if ( $file ) {
			theme_vive_send_page( $file );
		}
	}
	
	$file = false;
	// The comparison page belongs to the main VIVE snapshot, although its
	// public legacy URL is under /shop/products/. Do not look for it in the
	// Shopify product-detail directory, where no comparison snapshot exists.
	if ( preg_match( '#^/shop/products/comparison/?$#i', $request ) ) {
		$file = theme_vive_page_file( 'vive.com', '/product/comparison/' );
		if ( $file ) {
			theme_vive_send_page( $file );
		}
	}
	if ( 0 === strpos( $request, '/blog/' ) ) {
		$database_post = theme_vive_blog_find_post( substr( $request, 6 ) );
		if ( $database_post ) {
			$file = theme_vive_page_file( 'blog.vive.com', substr( $request, 6 ) );
			if ( ! $file ) { $file = theme_vive_blog_template_file(); }
			if ( $file ) {
				global $theme_vive_blog_post; $theme_vive_blog_post = $database_post;
				theme_vive_send_page( $file );
			}
		}
	}
	
	// 4. Kiểm tra URL sản phẩm dạng /shop/products/{slug}/
	if ( preg_match( '#^/shop/products/(?<slug>[^/]+)/?$#i', $request, $m_prod ) && 'comparison' !== strtolower( $m_prod['slug'] ) ) {
		$prod_slug = strtolower( trim( $m_prod['slug'] ) );

		// Tự động chuyển hướng các biến thể SKU con của VIVE Eagle về trang chính Kính AI VIVE Eagle
		if ( preg_match( '#^(?:htc-)?vive-eagle-(?:pantos|square|glossy)-#i', $prod_slug ) ) {
			wp_safe_redirect( home_url( '/shop/products/vive-eagle/' ), 301 );
			exit;
		}

		$file = theme_vive_page_file( 'shop-us.vive.com', '/products/' . $prod_slug . '/' );
		$database_product = function_exists( 'theme_vive_find_product' ) ? theme_vive_find_product( $prod_slug ) : null;
		$is_dynamic = false;
		if ( ! $file && $database_product && function_exists( 'theme_vive_product_template_file' ) ) {
			$file = theme_vive_product_template_file();
			$is_dynamic = true;
		}
		if ( $file ) {
			if ( $is_dynamic && $database_product ) {
				global $theme_vive_current_product;
				$theme_vive_current_product = $database_product;
			}
			theme_vive_send_page( $file );
		}
	}

	// 4b. Kiểm tra URL dạng /shop/...
	if ( 0 === strpos( $request, '/shop/' ) || '/shop' === $request ) {
		$path = substr( $request, 5 );
		$file = theme_vive_page_file( 'shop-us.vive.com', $path ? $path : '/' );
	}
	// 5. Kiểm tra subdomain slugs như /business/..., /developer/..., /enterprise/..., /arts/...
	elseif ( preg_match( '#^/(?<slug>business|developer|enterprise|service|blog|mars|sync|viverse|arts|locator|roundtable|vivex|vr4impact|hub|store)(?<path>/.*)?$#i', $request, $m ) ) {
		$host = theme_vive_slug_host( $m['slug'] );
		$path = ! empty( $m['path'] ) ? $m['path'] : '/';
		$file = theme_vive_page_file( $host, $path );
	}
	// 6. Mặc định: Tìm trong trang chính (pages/us/, pages/, footer-sites/vive.com)
	else {
		$file = theme_vive_page_file( 'vive.com', $request );
		if ( ! $file ) {
			$file = theme_vive_page_file( 'blog.vive.com', $request );
		}
	}
	
	if ( ! $file ) {
		if ( 0 === strpos( $request, '/blog' ) ) {
			$file = theme_vive_page_file( 'blog.vive.com', '/' );
			if ( $file ) {
				theme_vive_send_page( $file );
			}
		}
		// A missing mirror route must remain a real 404. Redirecting every unknown
		// URL to the product archive creates soft-404s and hides broken links.
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		$template = get_404_template();
		if ( $template ) {
			include $template;
		}
		exit;
	}
	
	theme_vive_send_page( $file );
}
add_action( 'template_redirect', 'theme_vive_router', 0 );
