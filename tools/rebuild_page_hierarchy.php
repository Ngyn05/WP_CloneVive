<?php
/**
 * Rebuild Full Hierarchical Tree for All Pages in WordPress DB (Windows-safe path handling).
 */
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$theme_root = dirname( __DIR__ );

$wc_cart_id     = (int) get_option( 'woocommerce_cart_page_id' );
$wc_checkout_id = (int) get_option( 'woocommerce_checkout_page_id' );
$wc_myacct_id   = (int) get_option( 'woocommerce_myaccount_page_id' );
$wc_shop_id     = (int) get_option( 'woocommerce_shop_page_id' );
$protected_ids  = array( $wc_cart_id, $wc_checkout_id, $wc_myacct_id, $wc_shop_id );

function normalize_route( $path ) {
	$path = str_replace( '\\', '/', (string) $path );
	$path = '/' . trim( $path, '/' );
	return ( '/' === $path ) ? '/' : $path . '/';
}

function get_parent_route( $route ) {
	$route = normalize_route( $route );
	if ( '/' === $route ) {
		return false;
	}
	$trimmed = trim( $route, '/' );
	$parts   = explode( '/', $trimmed );
	if ( count( $parts ) <= 1 ) {
		return '/';
	}
	array_pop( $parts );
	return '/' . implode( '/', $parts ) . '/';
}

$section_titles = array(
	'newsroom'          => 'Tin tức & Sự kiện VIVE',
	'solution'          => 'Giải pháp thực tế ảo VIVE',
	'vive-hub'          => 'Trung tâm ứng dụng VIVE Hub',
	'business'          => 'Giải pháp thực tế ảo dành cho Doanh nghiệp',
	'developer'         => 'Cổng thông tin & Tài nguyên Nhà phát triển',
	'enterprise'        => 'Giải pháp VR/XR cấp Doanh nghiệp lớn',
	'arts'              => 'VIVE Arts - Nghệ thuật & Văn hóa số VR',
	'locator'           => 'Tìm kiếm đại lý & Trung tâm trải nghiệm VIVE',
	'roundtable'        => 'Diễn đàn thảo luận VIVE Roundtable',
	'vivex'             => 'Quỹ khởi nghiệp & Tăng tốc VIVE X',
	'vr4impact'         => 'VR for Impact - Thực tế ảo vì cộng đồng',
	'sync'              => 'VIVE Sync - Nền tảng hội thảo ảo 3D',
	'hub'               => 'Ứng dụng & Nền tảng VIVE Hub',
	'legal'             => 'Thông tin Pháp lý & Điều khoản',
	'setup'             => 'Hướng dẫn thiết lập thiết bị VIVE',
	'support'           => 'Trung tâm hỗ trợ kỹ thuật VIVE',
	'accessory'         => 'Phụ kiện kính thực tế ảo VIVE',
	'product'           => 'Kính thực tế ảo VIVE Độc lập & PC VR',
	'vive-focus-vision' => 'VIVE Focus Vision',
	'vive-pro2'         => 'VIVE Pro 2',
	'vive-pro2-full-kit'=> 'VIVE Pro 2 Full Kit',
	'vive-xr-elite'     => 'VIVE XR Elite',
	'vive-flow'         => 'VIVE Flow',
	'vive-eagle'        => 'VIVE Eagle',
	'business-plus'     => 'Nền tảng VIVE Business+',
	'dms'               => 'Hệ thống quản lý thiết bị DMS',
	'vive-lbss'         => 'Hệ thống VIVE LBS',
	'category_howto'    => 'Hướng dẫn sử dụng chi tiết',
	'faq'               => 'Câu hỏi thường gặp',
	'stories'           => 'Câu chuyện thành công',
	'resources'         => 'Tài nguyên & Tài liệu phát triển',
	'hardware'          => 'Tài liệu phần cứng',
	'hardware-guides'   => 'Hướng dẫn kỹ thuật phần cứng',
	'events'            => 'Sự kiện & Hội thảo',
	'us'                => 'Khu vực chính',
	'home'              => 'Trang tổng quan',
);

$json_file = dirname( __DIR__ ) . '/scratch/all_vietnamese_titles.json';
$titles_map = array();
if ( is_file( $json_file ) ) {
	$raw = json_decode( file_get_contents( $json_file ), true );
	if ( is_array( $raw ) ) {
		foreach ( $raw as $item ) {
			$titles_map[ normalize_route( $item['route'] ) ] = $item['title'];
		}
	}
}

// 1. Scan and collect all existing pages
$existing_pages = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'any',
	'posts_per_page' => -1,
) );

$db_by_route = array();
foreach ( $existing_pages as $p ) {
	$r = get_post_meta( $p->ID, '_theme_vive_mirror_route', true );
	if ( $r ) {
		$db_by_route[ normalize_route( $r ) ] = $p->ID;
	}
}

// Ensure all ancestor routes exist in $db_by_route
function ensure_route_exists( $route ) {
	global $db_by_route, $titles_map, $section_titles;

	$route = normalize_route( $route );
	if ( isset( $db_by_route[ $route ] ) ) {
		return $db_by_route[ $route ];
	}

	$parent_id = 0;
	$parent_route = get_parent_route( $route );
	if ( false !== $parent_route && '/' !== $parent_route ) {
		$parent_id = ensure_route_exists( $parent_route );
	} elseif ( '/' === $parent_route && isset( $db_by_route['/'] ) ) {
		$parent_id = $db_by_route['/'];
	}

	$segments = array_values( array_filter( explode( '/', trim( $route, '/' ) ) ) );
	$slug     = $segments ? sanitize_title( end( $segments ) ) : 'trang-chu';
	$raw_slug = $segments ? end( $segments ) : 'trang-chu';

	$title = isset( $titles_map[ $route ] ) ? $titles_map[ $route ] : ( isset( $section_titles[ $raw_slug ] ) ? $section_titles[ $raw_slug ] : ucwords( str_replace( array( '-', '_' ), ' ', $raw_slug ) ) );

	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_parent'  => $parent_id,
		'post_content' => '',
		'meta_input'   => array(
			'_theme_vive_mirror_route' => $route,
		),
	), true );

	if ( ! is_wp_error( $page_id ) ) {
		$db_by_route[ $route ] = $page_id;
		echo "Created missing ancestor page [ID: {$page_id}] '{$title}' for: {$route} (parent: {$parent_id})\n";
		return $page_id;
	}
	return 0;
}

$all_known_routes = array_keys( $db_by_route );
foreach ( $all_known_routes as $r ) {
	ensure_route_exists( $r );
}

// 2. Update post_parent, post_name, post_title for all pages
$updated = 0;
foreach ( $db_by_route as $route => $pid ) {
	if ( in_array( $pid, $protected_ids, true ) ) {
		continue;
	}

	$parent_id = 0;
	$parent_route = get_parent_route( $route );
	if ( false !== $parent_route && '/' !== $parent_route && isset( $db_by_route[ $parent_route ] ) ) {
		$parent_id = $db_by_route[ $parent_route ];
	}

	$segments = array_values( array_filter( explode( '/', trim( $route, '/' ) ) ) );
	$slug     = $segments ? sanitize_title( end( $segments ) ) : 'trang-chu';
	$raw_slug = $segments ? end( $segments ) : 'trang-chu';

	$title = isset( $titles_map[ $route ] ) ? $titles_map[ $route ] : ( isset( $section_titles[ $raw_slug ] ) ? $section_titles[ $raw_slug ] : get_the_title( $pid ) );

	wp_update_post( array(
		'ID'          => $pid,
		'post_name'   => $slug,
		'post_parent' => $parent_id,
		'post_title'  => $title,
		'post_status' => 'publish',
	) );
	update_post_meta( $pid, '_theme_vive_mirror_route', $route );
	++$updated;
}

if ( isset( $db_by_route['/'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $db_by_route['/'] );
}

clean_post_cache( 0 );
wp_cache_flush();

echo "\nCompleted hierarchy update for {$updated} pages!\n";
