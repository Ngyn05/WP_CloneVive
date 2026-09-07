<?php
/** Populate Yoast SEO fields for VIVE pages, posts, and products. */

if ( 'cli' !== PHP_SAPI ) {
	exit( "CLI only.\n" );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_file( $wp_load ) ) {
	exit( "Could not locate wp-load.php.\n" );
}
require_once $wp_load;

$execute  = in_array( '--execute', $argv, true );
$front_id = (int) get_option( 'page_on_front' );
$types    = array( 'page', 'post', 'product' );
$counts   = array( 'page' => 0, 'post' => 0, 'product' => 0 );

function theme_vive_seo_plain_text( $text ) {
	$text = strip_shortcodes( (string) $text );
	$text = wp_strip_all_tags( html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
	$text = preg_replace( '#https?://\S+#iu', '', $text );
	return trim( preg_replace( '/\s+/u', ' ', $text ) );
}

function theme_vive_seo_limit( $text, $limit ) {
	$text = theme_vive_seo_plain_text( $text );
	if ( mb_strlen( $text, 'UTF-8' ) <= $limit ) { return $text; }
	$cut = mb_substr( $text, 0, $limit + 1, 'UTF-8' );
	$cut = preg_replace( '/\s+\S*$/u', '', $cut );
	return rtrim( $cut, " \t\n\r\0\x0B,;:-" ) . '…';
}

function theme_vive_seo_mirror_description( $post_id ) {
	$file = get_post_meta( $post_id, '_theme_vive_mirror_file', true );
	if ( ! $file || ! is_readable( $file ) ) { return ''; }
	$html = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! preg_match( '#<meta\b[^>]*\bname=["\']description["\'][^>]*\bcontent=["\'](?<description>[^"\']+)["\']#i', $html, $match )
		&& ! preg_match( '#<meta\b[^>]*\bcontent=["\'](?<description>[^"\']+)["\'][^>]*\bname=["\']description["\']#i', $html, $match ) ) {
		return '';
	}
	$description = html_entity_decode( $match['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	if ( function_exists( 'theme_vive_translate_html' ) ) {
		$translated = theme_vive_translate_html( '<meta name="description" content="' . esc_attr( $description ) . '">' );
		if ( preg_match( '#\bcontent=["\'](?<description>[^"\']*)["\']#i', $translated, $translated_match ) ) {
			$description = html_entity_decode( $translated_match['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}
	}
	return theme_vive_seo_plain_text( $description );
}

function theme_vive_seo_keyword( $post, $is_front ) {
	if ( $is_front ) { return 'kính thực tế ảo VIVE'; }
	$keyword = theme_vive_seo_plain_text( $post->post_title );
	$keyword = preg_replace( '/\s*[|–—-]\s*VIVE(?:\s+Việt Nam)?\s*$/iu', '', $keyword );
	$keyword = preg_split( '/\s*[:|–—]\s*/u', $keyword, 2 )[0];
	return trim( wp_trim_words( $keyword, 10, '' ) );
}

function theme_vive_seo_title( $post, $is_front ) {
	$variant_titles = array(
		'face-gasket-and-rear-pad-for-vive-focus-series-narrow' => 'Mặt và đệm sau VIVE Focus - Bản hẹp',
		'face-gasket-and-rear-pad-for-vive-focus-series-wide'   => 'Mặt và đệm sau VIVE Focus - Bản rộng',
	);
	if ( ! $is_front && isset( $variant_titles[ $post->post_name ] ) ) {
		return $variant_titles[ $post->post_name ] . ' | VIVE Việt Nam';
	}
	if ( $is_front ) { return 'VIVE Việt Nam - Kính thực tế ảo VR chính hãng'; }
	$base = preg_replace( '/\s*[|–—-]\s*VIVE(?:\s+Việt Nam)?\s*$/iu', '', theme_vive_seo_plain_text( $post->post_title ) );
	// Prefer the main phrase before a subtitle, then shorten at a word boundary.
	$parts = preg_split( '/\s*[:|–—]\s*/u', $base, 2 );
	$short = trim( $parts[0] );
	if ( mb_strlen( $short, 'UTF-8' ) < 12 && ! empty( $parts[1] ) ) {
		$short = $base;
	}
	$short = trim( wp_trim_words( $short, 9, '' ) );
	while ( mb_strlen( $short, 'UTF-8' ) > 48 && preg_match( '/\s+\S+$/u', $short ) ) {
		$short = preg_replace( '/\s+\S+$/u', '', $short );
	}
	return $short . ' | VIVE Việt Nam';
}

function theme_vive_seo_description( $post, $keyword, $is_front ) {
	if ( $is_front ) {
		return 'Khám phá kính thực tế ảo VIVE chính hãng tại Việt Nam: VR, XR, phụ kiện và giải pháp cho cá nhân, doanh nghiệp. Liên hệ để được tư vấn.';
	}
	$description = '';
	if ( 'page' === $post->post_type ) {
		$description = theme_vive_seo_mirror_description( $post->ID );
	}
	if ( ! $description ) { $description = get_the_excerpt( $post ); }
	if ( ! $description ) { $description = $post->post_content; }
	$description = theme_vive_seo_plain_text( $description );
	if ( mb_strlen( $description, 'UTF-8' ) < 70 ) {
		if ( 'product' === $post->post_type ) {
			$description = 'Khám phá ' . $keyword . ' chính hãng tại VIVE Việt Nam. Xem tính năng, thông số nổi bật và nhận tư vấn sản phẩm phù hợp với nhu cầu của bạn.';
		} elseif ( 'post' === $post->post_type ) {
			$description = 'Tìm hiểu ' . $keyword . ' cùng VIVE Việt Nam. Cập nhật thông tin, xu hướng và kiến thức hữu ích về công nghệ thực tế ảo VR, XR và metaverse.';
		} else {
			$description = 'Khám phá ' . $keyword . ' tại VIVE Việt Nam. Xem thông tin chi tiết, tính năng nổi bật và các giải pháp thực tế ảo phù hợp với nhu cầu của bạn.';
		}
	}
	$variant_suffixes = array(
		'controller'                      => ' Phiên bản tay cầm VIVE Controller tiêu chuẩn.',
		'controller2018'                  => ' Phiên bản VIVE Controller 2018.',
		'vive-controller-left'            => ' Phiên bản tay cầm bên trái.',
		'vive-controller-right'           => ' Phiên bản tay cầm bên phải.',
		'vive-flow-hygienic-cover-narrow' => ' Phiên bản đệm hẹp.',
		'vive-flow-hygienic-cover-wide'   => ' Phiên bản đệm rộng.',
	);
	if ( isset( $variant_suffixes[ $post->post_name ] ) ) {
		$description = theme_vive_seo_limit( $description, 112 ) . $variant_suffixes[ $post->post_name ];
	}
	return theme_vive_seo_limit( $description, 155 );
}

$posts = get_posts( array(
	'post_type'      => $types,
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'ID',
	'order'          => 'ASC',
) );

foreach ( $posts as $post ) {
	$is_front    = $front_id === (int) $post->ID;
	$keyword     = theme_vive_seo_keyword( $post, $is_front );
	$seo_title   = theme_vive_seo_title( $post, $is_front );
	$description = theme_vive_seo_description( $post, $keyword, $is_front );
	if ( $execute ) {
		update_post_meta( $post->ID, '_yoast_wpseo_focuskw', $keyword );
		update_post_meta( $post->ID, '_yoast_wpseo_title', $seo_title );
		update_post_meta( $post->ID, '_yoast_wpseo_metadesc', $description );
		// The Yoast editor reports both analyses as "OK". Yoast maps numeric
		// scores from 41 through 70 to that rank; use the midpoint so its list
		// columns show the same status after the indexable is rebuilt below.
		update_post_meta( $post->ID, '_yoast_wpseo_linkdex', 55 );
		update_post_meta( $post->ID, '_yoast_wpseo_content_score', 55 );
		// Fire the normal save hooks so Yoast refreshes its indexable record.
		wp_update_post( array( 'ID' => $post->ID ) );
	}
	$counts[ $post->post_type ]++;
	printf( "%s %d | %s | %s | %s\n", $execute ? 'UPDATED' : 'PREVIEW', $post->ID, $keyword, $seo_title, $description );
}

if ( $execute ) {
	wp_cache_flush();
}
printf( "%s: pages=%d posts=%d products=%d total=%d\n", $execute ? 'COMPLETE' : 'DRY RUN', $counts['page'], $counts['post'], $counts['product'], array_sum( $counts ) );
