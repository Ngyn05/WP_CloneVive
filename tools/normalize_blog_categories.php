<?php
/** One-time normalization for imported VIVE blog categories. */

require dirname( __DIR__, 4 ) . '/wp-load.php';

if ( PHP_SAPI !== 'cli' ) {
	exit( "CLI only.\n" );
}

$canonical = array(
	30 => array( 'name' => 'Thực tế mở rộng (XR)', 'slug' => 'thuc-te-mo-rong-xr' ),
	32 => array( 'name' => 'Trí tuệ nhân tạo (AI)', 'slug' => 'tri-tue-nhan-tao-ai' ),
	33 => array( 'name' => 'Vũ trụ ảo (Metaverse)', 'slug' => 'vu-tru-ao-metaverse' ),
	34 => array( 'name' => 'Trò chơi & Giải trí', 'slug' => 'tro-choi-giai-tri' ),
	31 => array( 'name' => 'Doanh nghiệp', 'slug' => 'doanh-nghiep' ),
	37 => array( 'name' => 'Tin tức VIVE', 'slug' => 'tin-tuc-vive' ),
	26 => array( 'name' => 'Sản phẩm', 'slug' => 'san-pham' ),
	41 => array( 'name' => 'Sự kiện & Hoạt động', 'slug' => 'su-kien-hoat-dong' ),
);

// Imported English, Chinese and duplicate Vietnamese terms => canonical term.
$merge = array(
	3 => 30,
	5 => 32, 18 => 32, 40 => 32,
	6 => 33, 17 => 33,
	7 => 34, 42 => 34,
	4 => 31, 8 => 31, 11 => 31, 19 => 31, 27 => 31, 36 => 31,
	9 => 37, 35 => 37,
	10 => 26, 12 => 26, 14 => 26, 16 => 26, 25 => 26, 38 => 26, 39 => 26,
	13 => 41, 28 => 41,
);

foreach ( $merge as $source_id => $target_id ) {
	$source = get_term( $source_id, 'category' );
	$target = get_term( $target_id, 'category' );
	if ( ! $source || is_wp_error( $source ) || ! $target || is_wp_error( $target ) ) {
		continue;
	}

	$posts = get_objects_in_term( $source_id, 'category' );
	if ( ! is_wp_error( $posts ) ) {
		foreach ( $posts as $post_id ) {
			wp_set_post_categories( $post_id, array( $target_id ), true );
		}
	}

	$result = wp_delete_term( $source_id, 'category' );
	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, "Could not delete category {$source_id}: {$result->get_error_message()}\n" );
	}
}

foreach ( $canonical as $term_id => $values ) {
	$result = wp_update_term( $term_id, 'category', $values );
	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, "Could not update category {$term_id}: {$result->get_error_message()}\n" );
	}
}

clean_term_cache( array_keys( $canonical ), 'category' );
echo "Blog categories normalized.\n";
