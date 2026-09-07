<?php
/**
 * Mock REST API, ecmapping endpoints & static assets handler for VIVE theme comparison & dynamic features.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function theme_vive_translate_data_recursive( $data, $trans_map, $parent_key = '' ) {
	if ( is_string( $data ) ) {
		// Bỏ qua các key hệ thống quan trọng
		if ( in_array( $parent_key, array( 'id', 'slug', 'code', 'spec', 'images', 'category_id', 'disclaimer_id', 'store_locale', 'ec_locale', 'cart_provider_type', 'currency' ), true ) ) {
			return $data;
		}
		$trimmed = trim( $data );
		if ( isset( $trans_map[ $trimmed ] ) ) {
			return $trans_map[ $trimmed ];
		}
		// Xử lý HTML trong content của specs
		if ( false !== strpos( $data, '<' ) ) {
			$data = preg_replace_callback( '#>(?<text>[^<>]+)<#s', static function( $m ) use ( $trans_map ) {
				$raw = $m['text'];
				$key = trim( html_entity_decode( $raw, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
				if ( isset( $trans_map[ $key ] ) ) {
					$left  = substr( $raw, 0, strlen( $raw ) - strlen( ltrim( $raw ) ) );
					$right = substr( $raw, strlen( rtrim( $raw ) ) );
					return '>' . $left . $trans_map[ $key ] . $right . '<';
				}
				return '>' . $raw . '<';
			}, $data );
		}
		return $data;
	}
	if ( is_array( $data ) ) {
		foreach ( $data as $k => $v ) {
			// Bỏ qua object category để không làm hỏng category.name (VIVE, ACCESSORY)
			if ( 'category' === $k && is_array( $v ) ) {
				continue;
			}
			$data[ $k ] = theme_vive_translate_data_recursive( $v, $trans_map, (string) $k );
		}
	}
	return $data;
}

function theme_vive_handle_rest_endpoints() {
	if ( is_admin() ) { return; }

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$path = (string) wp_parse_url( $request_uri, PHP_URL_PATH );
	$theme_dir = get_template_directory();

	// 0. Static asset server (phục vụ ảnh, media, font, css, js trực tiếp từ thư mục assets)
	if ( preg_match( '#^/(?:media|static|express_static|express_media)/#i', $path ) ) {
		$file = $theme_dir . '/assets' . rawurldecode( strtok( $path, '?#' ) );
		$ext = strtolower( pathinfo( strtok( $path, '?#' ), PATHINFO_EXTENSION ) );
		$mimes = array(
			'png'   => 'image/png',
			'jpg'   => 'image/jpeg',
			'jpeg'  => 'image/jpeg',
			'gif'   => 'image/gif',
			'webp'  => 'image/webp',
			'svg'   => 'image/svg+xml',
			'ico'   => 'image/x-icon',
			'css'   => 'text/css; charset=utf-8',
			'js'    => 'application/javascript; charset=utf-8',
			'json'  => 'application/json; charset=utf-8',
			'woff2' => 'font/woff2',
			'woff'  => 'font/woff',
			'ttf'   => 'font/ttf',
			'mp4'   => 'video/mp4',
			'webm'  => 'video/webm',
		);
		$mime = isset( $mimes[ $ext ] ) ? $mimes[ $ext ] : 'application/octet-stream';
		if ( is_file( $file ) ) {
			header( 'Content-Type: ' . $mime );
			header( 'Cache-Control: public, max-age=604800, immutable' );
			readfile( $file );
			exit;
		}
		// Asset không tồn tại cục bộ: trả về fallback tức thì trong 1ms, không chuyển hướng vào WordPress PHP
		// Imported blog bodies can reference original media that was not bundled.
		// Redirect those files just like the public renderer instead of showing
		// the generic grey placeholder in Gutenberg.
		if ( 0 === stripos( $path, '/express_media/' ) && in_array( $ext, array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' ), true ) ) {
			wp_redirect( 'https://blog.vive.com' . $path, 302, 'VIVE media fallback' );
			exit;
		}
		if ( in_array( $ext, array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico' ), true ) ) {
			header( 'Content-Type: image/svg+xml' );
			header( 'Cache-Control: public, max-age=86400' );
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1" viewBox="0 0 1 1"><rect width="1" height="1" fill="#f0f0f0"/></svg>';
			exit;
		}
		status_header( 404 );
		exit;
	}

	// 1. /redirect/ecmapping/
	if ( preg_match( '#^/redirect/ecmapping/#i', $path ) ) {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Cache-Control: public, max-age=3600' );
		$cookie_data = array(
			// The mirrored page declares data-site="us" and its bundled app only
			// contains an en_us_s1_v5 country record. These identifiers must match
			// or the app dereferences an undefined country before mounting compare.
			'locale'           => 'vive_us',
			'fiterCountry'     => 'en_us_s1_v5',
			'ecLocale'         => 'vive_us',
			'currency'         => 'VND',
			'themeId'          => '',
			'countryName'      => 'Việt Nam',
			'siteId'           => 'us',
			'storeLocale'      => 'en_us_s1_v5',
			'cartProviderType' => 'SHOPIFY',
			'miniCartApiLink'  => '',
			'priceFormat'      => '',
		);
		$cookie_val = rawurlencode( wp_json_encode( $cookie_data ) );
		echo 'document.cookie="dr-htc-locale-info=' . $cookie_val . '; path=/;";';
		exit;
	}

	// 2. /rest/ecmapping/ControlVersion/VIVEWidget
	if ( preg_match( '#^/rest/ecmapping/ControlVersion/VIVEWidget#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( array( 'value' => array( 'version' => '202503251' ) ) );
		exit;
	}

	// 3. /rest/products/
	if ( preg_match( '#^/rest/products/?#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		$file = $theme_dir . '/inc/data/products.json';
		if ( is_readable( $file ) ) {
			$raw = file_get_contents( $file );
			$data = json_decode( $raw, true );
			if ( is_array( $data ) ) {
				$trans_map = theme_vive_translation_map();
				$data = theme_vive_translate_data_recursive( $data, $trans_map );
				// Chuẩn hóa đường dẫn images
				if ( isset( $data['products'] ) && is_array( $data['products'] ) ) {
					foreach ( $data['products'] as &$p ) {
						if ( ! empty( $p['images'] ) && function_exists( 'theme_vive_asset_url' ) ) {
							$asset_url = theme_vive_asset_url( $p['images'] );
							if ( $asset_url ) {
								$p['images'] = $asset_url;
							}
						}
					}
					unset( $p );
				}
				echo wp_json_encode( $data );
				exit;
			}
		}
		echo wp_json_encode( array( 'products' => array() ) );
		exit;
	}

	// 4. /rest/disclaimers/
	if ( preg_match( '#^/rest/disclaimers/(?<lang>[^/]+)/(?<type>[^/]+)#i', $path, $m ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		$type = strtoupper( $m['type'] );
		$file = ( 'ACCESSORY' === $type ) ? $theme_dir . '/inc/data/disclaimers-accessory.json' : $theme_dir . '/inc/data/disclaimers-vive.json';
		if ( is_readable( $file ) ) {
			$raw = file_get_contents( $file );
			$data = json_decode( $raw, true );
			if ( is_array( $data ) ) {
				$trans_map = theme_vive_translation_map();
				$data = theme_vive_translate_data_recursive( $data, $trans_map );
				echo wp_json_encode( $data );
				exit;
			}
		}
		echo wp_json_encode( array( 'count' => 0, 'results' => array() ) );
		exit;
	}

	// 5. /rest/product/
	if ( preg_match( '#^/rest/product/?#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		$file = $theme_dir . '/inc/data/product-slugs.json';
		if ( is_readable( $file ) ) {
			$raw = file_get_contents( $file );
			$data = json_decode( $raw, true );
			if ( is_array( $data ) ) {
				$trans_map = theme_vive_translation_map();
				$data = theme_vive_translate_data_recursive( $data, $trans_map );
				echo wp_json_encode( $data );
				exit;
			}
		}
		echo wp_json_encode( array( 'count' => 0, 'results' => array() ) );
		exit;
	}

	// 6. /rest/stayupdate/countries/
	if ( preg_match( '#^/rest/stayupdate/countries/?#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		$file = $theme_dir . '/inc/data/stayupdate-countries.json';
		if ( is_readable( $file ) ) {
			readfile( $file );
			exit;
		}
		echo wp_json_encode( array( 'count' => 0, 'results' => array() ) );
		exit;
	}

	// 7. /api/v2/vive-blog/
	if ( preg_match( '#^/api/v2/vive-blog/?#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		$database_response = function_exists( 'theme_vive_blog_api_response' ) ? theme_vive_blog_api_response() : null;
		if ( $database_response ) { echo wp_json_encode( $database_response ); exit; }
		$file = $theme_dir . '/inc/data/blog-articles-full.json';
		if ( is_readable( $file ) ) {
			$raw = file_get_contents( $file );
			$data = json_decode( $raw, true );
			if ( is_array( $data ) && isset( $data['items'] ) ) {
				$limit  = isset( $_GET['limit'] ) ? max( 1, (int) $_GET['limit'] ) : 10;
				$offset = isset( $_GET['offset'] ) ? max( 0, (int) $_GET['offset'] ) : 0;
				$total  = count( $data['items'] );
				$paged_items = array_slice( $data['items'], $offset, $limit );
				$resp = array(
					'meta'  => array(
						'total_count' => $total,
					),
					'items' => $paged_items,
				);
				echo wp_json_encode( $resp );
				exit;
			}
		}
		echo wp_json_encode( array( 'meta' => array( 'total_count' => 0 ), 'items' => array() ) );
		exit;
	}

	// 8. /api/v2/pages/ (Tags API)
	if ( preg_match( '#^/api/v2/pages/?#i', $path ) ) {
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );
		$file = $theme_dir . '/inc/data/blog-tags.json';
		if ( is_readable( $file ) ) {
			readfile( $file );
			exit;
		}
		echo wp_json_encode( array( 'meta' => array( 'total_count' => 0 ), 'items' => array() ) );
		exit;
	}
}
add_action( 'init', 'theme_vive_handle_rest_endpoints', 1 );
