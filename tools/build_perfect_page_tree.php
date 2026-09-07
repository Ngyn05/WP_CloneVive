<?php
/**
 * Create all missing parent pages and build a 100% perfect WordPress page hierarchy.
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

// 1. Fetch all existing pages in DB
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

// Collect all routes that need to exist (including all ancestors)
$target_routes = array();
foreach ( array_keys( $db_by_route ) as $r ) {
	$trimmed = trim( $r, '/' );
	if ( '' === $trimmed ) {
		$target_routes['/'] = true;
		continue;
	}
	$parts = explode( '/', $trimmed );
	$accum = '';
	foreach ( $parts as $p ) {
		$accum .= '/' . $p;
		$target_routes[ $accum . '/' ] = true;
	}
}

// Sort all routes by depth (shallow to deep)
$all_routes = array_keys( $target_routes );
usort( $all_routes, static function ( $a, $b ) {
	$da = substr_count( $a, '/' );
	$db = substr_count( $b, '/' );
	return ( $da !== $db ) ? ( $da <=> $db ) : strnatcasecmp( $a, $b );
} );

echo "Total target routes (including all ancestors): " . count( $all_routes ) . "\n\n";

$created_count = 0;
$updated_count = 0;

foreach ( $all_routes as $route ) {
	// Find parent route
	$parent_id = 0;
	if ( '/' !== $route ) {
		$trimmed = trim( $route, '/' );
		$parts   = explode( '/', $trimmed );
		if ( count( $parts ) > 1 ) {
			array_pop( $parts );
			$parent_route = '/' . implode( '/', $parts ) . '/';
			if ( isset( $db_by_route[ $parent_route ] ) ) {
				$parent_id = $db_by_route[ $parent_route ];
			}
		}
	}

	$segments = array_values( array_filter( explode( '/', trim( $route, '/' ) ) ) );
	$slug     = $segments ? sanitize_title( end( $segments ) ) : 'trang-chu';
	$raw_slug = $segments ? end( $segments ) : 'trang-chu';

	$title = '';
	if ( isset( $titles_map[ $route ] ) ) {
		$title = $titles_map[ $route ];
	} elseif ( isset( $section_titles[ $raw_slug ] ) ) {
		$title = $section_titles[ $raw_slug ];
	} elseif ( isset( $section_titles[ $slug ] ) ) {
		$title = $section_titles[ $slug ];
	} else {
		$title = ucwords( str_replace( array( '-', '_' ), ' ', $raw_slug ) );
	}

	if ( isset( $db_by_route[ $route ] ) ) {
		$page_id = $db_by_route[ $route ];
		if ( ! in_array( $page_id, $protected_ids, true ) ) {
			wp_update_post( array(
				'ID'          => $page_id,
				'post_name'   => $slug,
				'post_parent' => $parent_id,
				'post_title'  => $title,
				'post_status' => 'publish',
			) );
			update_post_meta( $page_id, '_theme_vive_mirror_route', $route );
			++$updated_count;
		}
	} else {
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
			echo "Created ancestor page [ID: {$page_id}] '{$title}' for: {$route} (parent: {$parent_id})\n";
			++$created_count;
		} else {
			echo "Failed to create {$route}: " . $page_id->get_error_message() . "\n";
		}
	}
}

if ( isset( $db_by_route['/'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $db_by_route['/'] );
}

clean_post_cache( 0 );
wp_cache_flush();

echo "\nSummary: Created {$created_count} ancestor pages, Updated {$updated_count} pages.\n";
