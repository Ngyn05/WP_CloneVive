<?php
/**
 * Rebuild WordPress Pages from the mirrored files in pages/us.
 *
 * Usage:
 * php tools/import-project-pages.php          # preview only
 * php tools/import-project-pages.php --execute
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( "This importer can only run from the command line.\n" );
}

define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$execute    = in_array( '--execute', $argv, true );
$retitle    = in_array( '--retitle', $argv, true );
$list_orphans = in_array( '--list-orphans', $argv, true );
$theme_root = dirname( __DIR__ );
$pages_root = $theme_root . '/pages/us';

if ( ! is_dir( $pages_root ) ) {
	fwrite( STDERR, "Missing pages directory: {$pages_root}\n" );
	exit( 1 );
}

/** Convert an HTML file path into its public mirrored route. */
function theme_vive_import_route( $file, $root ) {
	$relative = str_replace( '\\', '/', substr( $file, strlen( $root ) + 1 ) );
	$relative = preg_replace( '#\.html?$#i', '', $relative );
	$relative = preg_replace( '#(?:^|/)index$#i', '', $relative );
	$route    = '/' . trim( $relative, '/' );
	return '/' === $route ? '/' : $route . '/';
}

/** Read a useful title without importing the large mirrored HTML document. */
function theme_vive_import_title( $file, $route ) {
	$handle = fopen( $file, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$head   = $handle ? fread( $handle, 262144 ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( $handle ) {
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	if ( preg_match( '#<title\b[^>]*>(.*?)</title>#is', (string) $head, $match ) ) {
		$title = trim( wp_strip_all_tags( html_entity_decode( $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
		$title = preg_replace( '#\s*[|\-–—]\s*(?:HTC\s+)?VIVE.*$#iu', '', $title );
		if ( '' !== $title ) {
			return $title;
		}
	}
	if ( '/' === $route ) {
		return 'Trang chủ';
	}
	$parts = explode( '/', trim( $route, '/' ) );
	return ucwords( str_replace( array( '-', '_' ), ' ', end( $parts ) ) );
}

/** Apply the same Vietnamese dictionary and trademark fixes as the frontend. */
function theme_vive_import_vietnamese_title( $title ) {
	if ( function_exists( 'theme_vive_translate_html' ) ) {
		$title = theme_vive_translate_html( '<span>' . esc_html( $title ) . '</span>' );
		$title = html_entity_decode( wp_strip_all_tags( $title ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}
	$title = str_ireplace(
		array(
			'Awarding Winning VR Film',
			'All-in-one Headset Setup',
			'Immersive Glasses Setup',
			'PC VR Headset Setup',
			'Facial Tracker for VIVE Focus Series',
			'Narrow Face Cushion Set for the VIVE Pro',
			'New Standalone PC VR Headset for Gaming',
			'Specs for the Standalone PC VR Headset',
			'Full Kit Specs',
			'VIVE Pro 2 Specs',
			'VIVE Eagle Spec',
			'Set Up Your VIVE',
			'Official VIVE Support',
			'Multi-Display VR and Mixed Reality Desktop',
			'Mini DisplayPort-DisplayPort Cable',
			'Case for Immersive VR Glasses',
			'Eye-Opening Mixed Reality',
			'PC VR Gaming for VIVE XR Elite',
			'Setting up the Wireless Adapter',
			'SteamVR Base Station 2.0',
		),
		array(
			'Phim VR từng đoạt giải',
			'Thiết lập kính VIVE độc lập',
			'Thiết lập kính thực tế ảo VIVE',
			'Thiết lập kính VIVE PC VR',
			'Bộ theo dõi khuôn mặt cho dòng VIVE Focus',
			'Bộ đệm mặt hẹp cho VIVE Pro',
			'Kính PC VR độc lập mới dành cho chơi game',
			'Thông số kính PC VR độc lập',
			'Thông số bộ đầy đủ',
			'Thông số VIVE Pro 2',
			'Thông số VIVE Eagle',
			'Thiết lập VIVE của bạn',
			'Trung tâm hỗ trợ VIVE chính thức',
			'Máy tính thực tế hỗn hợp và VR đa màn hình',
			'Cáp Mini DisplayPort–DisplayPort',
			'Hộp đựng kính thực tế ảo',
			'Trải nghiệm thực tế hỗn hợp mở rộng tầm nhìn',
			'Chơi game PC VR cho VIVE XR Elite',
			'Thiết lập bộ điều hợp không dây',
			'Trạm cơ sở SteamVR 2.0',
		),
		$title
	);
	return trim( preg_replace( '/\s+/', ' ', $title ) );
}

$routes   = array();
$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator( $pages_root, FilesystemIterator::SKIP_DOTS )
);

foreach ( $iterator as $entry ) {
	if ( ! $entry->isFile() || ! preg_match( '#\.html?$#i', $entry->getFilename() ) ) {
		continue;
	}
	// Standalone HTML files are individual support articles. The Pages CMS is
	// reserved for actual site/landing pages represented by directory indexes.
	if ( ! preg_match( '#^index\.html?$#i', $entry->getFilename() ) ) {
		continue;
	}
	$file  = $entry->getPathname();
	$route = theme_vive_import_route( $file, $pages_root );
	if ( preg_match( '#template[\s_-]*mau[\s_-]*64|templatemau64#i', $route . ' ' . $file ) ) {
		continue;
	}
	// Prefer a directory index when both foo.html and foo/index.html exist.
	$is_index = preg_match( '#^index\.html?$#i', $entry->getFilename() );
	if ( ! isset( $routes[ $route ] ) || $is_index ) {
		$routes[ $route ] = array(
			'file'  => $file,
			'title' => theme_vive_import_title( $file, $route ),
		);
	}
}

// Keep only pages reachable through internal links, beginning at the home page.
// This excludes orphaned mirror files and old template/sample documents.
$all_routes       = $routes;
$reachable_routes = array();
$queue            = array( '/' );

while ( $queue ) {
	$route = array_shift( $queue );
	if ( isset( $reachable_routes[ $route ] ) || ! isset( $all_routes[ $route ] ) ) {
		continue;
	}
	$reachable_routes[ $route ] = $all_routes[ $route ];
	$html = file_get_contents( $all_routes[ $route ]['file'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! preg_match_all( '#<a\b[^>]*\bhref\s*=\s*["\'](?<url>[^"\']+)["\']#i', (string) $html, $links ) ) {
		continue;
	}
	foreach ( $links['url'] as $url ) {
		$url = html_entity_decode( trim( $url ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( '' === $url || '#' === $url[0] || preg_match( '#^(?:javascript|mailto|tel):#i', $url ) ) {
			continue;
		}
		if ( 0 === strpos( $url, '//' ) ) {
			$url = 'https:' . $url;
		}
		if ( preg_match( '#^https?://#i', $url ) ) {
			$parts = wp_parse_url( $url );
			if ( empty( $parts['host'] ) || ! preg_match( '#(^|\.)vive\.com$#i', $parts['host'] ) ) {
				continue;
			}
			$url = isset( $parts['path'] ) ? $parts['path'] : '/';
		}
		if ( 0 !== strpos( $url, '/' ) ) {
			$url = trailingslashit( dirname( $route ) ) . $url;
		}
		$path = rawurldecode( (string) wp_parse_url( $url, PHP_URL_PATH ) );
		$path = preg_replace( '#^/us(?:/|$)#i', '/', $path );
		$path = preg_replace( '#/index\.html?$#i', '/', $path );
		$path = preg_replace( '#\.html?$#i', '', $path );
		$path = '/' . trim( preg_replace( '#/{2,}#', '/', $path ), '/' );
		$path = '/' === $path ? '/' : $path . '/';
		if ( isset( $all_routes[ $path ] ) && ! isset( $reachable_routes[ $path ] ) ) {
			$queue[] = $path;
		}
	}
}

$routes = $reachable_routes;

if ( $list_orphans ) {
	$orphan_routes = array_diff_key( $all_routes, $reachable_routes );
	uksort( $orphan_routes, 'strnatcasecmp' );
	echo 'Orphan index routes: ' . count( $orphan_routes ) . "\n";
	foreach ( $orphan_routes as $orphan_route => $orphan_page ) {
		echo $orphan_route . "\t" . $orphan_page['title'] . "\n";
	}
	exit( 0 );
}

uksort( $routes, static function ( $left, $right ) {
	$depth = substr_count( $left, '/' ) <=> substr_count( $right, '/' );
	return 0 !== $depth ? $depth : strnatcasecmp( $left, $right );
} );

$all_page_ids = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$protected_ids = array_filter(
	array_map(
		'absint',
		array(
			get_option( 'woocommerce_shop_page_id' ),
			get_option( 'woocommerce_cart_page_id' ),
			get_option( 'woocommerce_checkout_page_id' ),
			get_option( 'woocommerce_myaccount_page_id' ),
			get_option( 'woocommerce_terms_page_id' ),
		)
	)
);
$delete_ids = array_values( array_diff( $all_page_ids, $protected_ids ) );

if ( $retitle ) {
	$imported_ids = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_theme_vive_mirror_file', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	$updated   = 0;
	$unchanged = 0;
	$missing   = 0;
	foreach ( $imported_ids as $page_id ) {
		$relative = get_post_meta( $page_id, '_theme_vive_mirror_file', true );
		$route    = get_post_meta( $page_id, '_theme_vive_mirror_route', true );
		$file     = $theme_root . '/' . ltrim( str_replace( '\\', '/', $relative ), '/' );
		if ( ! is_file( $file ) ) {
			++$missing;
			continue;
		}
		$source_title = theme_vive_import_title( $file, $route );
		$new_title    = theme_vive_import_vietnamese_title( $source_title );
		if ( get_the_title( $page_id ) === $new_title ) {
			++$unchanged;
			continue;
		}
		$result = wp_update_post(
			array(
				'ID'         => $page_id,
				'post_title' => $new_title,
			),
			true
		);
		if ( ! is_wp_error( $result ) ) {
			++$updated;
		}
	}
	wp_cache_flush();
	echo "Retitle complete. Updated: {$updated}; unchanged: {$unchanged}; missing files: {$missing}.\n";
	exit( 0 );
}

echo 'All HTML routes found: ' . count( $all_routes ) . "\n";
echo 'Internally linked routes selected: ' . count( $routes ) . "\n";
echo 'Existing Pages: ' . count( $all_page_ids ) . "\n";
echo 'Pages to clear: ' . count( $delete_ids ) . "\n";
echo 'WooCommerce system Pages preserved: ' . count( $protected_ids ) . "\n";

if ( ! $execute ) {
	echo "Preview complete. Run again with --execute to rebuild the database.\n";
	exit( 0 );
}

foreach ( $delete_ids as $page_id ) {
	wp_delete_post( $page_id, true );
}

$created_by_route = array();
$created          = 0;
$failed           = 0;

foreach ( $routes as $route => $page ) {
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
	$page_id  = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => theme_vive_import_vietnamese_title( $page['title'] ),
			'post_name'    => $slug,
			'post_parent'  => $parent_id,
			'post_content' => '',
			'meta_input'   => array(
				'_theme_vive_mirror_route' => $route,
				'_theme_vive_mirror_file'  => str_replace( '\\', '/', substr( $page['file'], strlen( $theme_root ) + 1 ) ),
			),
		),
		true
	);

	if ( is_wp_error( $page_id ) ) {
		++$failed;
		fwrite( STDERR, "Failed {$route}: " . $page_id->get_error_message() . "\n" );
		continue;
	}
	$created_by_route[ $route ] = $page_id;
	++$created;
	if ( 0 === $created % 100 ) {
		echo "Imported {$created}/" . count( $routes ) . "\n";
	}
}

if ( isset( $created_by_route['/'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $created_by_route['/'] );
}

clean_post_cache( 0 );
wp_cache_flush();

echo "Import complete. Created: {$created}; failed: {$failed}.\n";
exit( $failed > 0 ? 2 : 0 );
