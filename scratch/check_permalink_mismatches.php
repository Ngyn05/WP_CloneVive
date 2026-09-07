<?php
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$pages = get_posts( array(
	'post_type'      => 'page',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
) );

$mismatches = array();
foreach ( $pages as $p ) {
	$route = get_post_meta( $p->ID, '_theme_vive_mirror_route', true );
	$file  = get_post_meta( $p->ID, '_theme_vive_mirror_file', true );
	$plink = wp_parse_url( get_permalink( $p->ID ), PHP_URL_PATH );
	$plink = '/' . trim( $plink, '/' );
	$plink = ( '/' === $plink ) ? '/' : $plink . '/';

	$clean_route = '/' . trim( (string) $route, '/' );
	$clean_route = ( '/' === $clean_route ) ? '/' : $clean_route . '/';

	if ( $route && $plink !== $clean_route ) {
		$mismatches[] = array(
			'id'          => $p->ID,
			'title'       => $p->post_title,
			'permalink'   => $plink,
			'route'       => $clean_route,
			'file'        => $file,
			'parent'      => $p->post_parent,
			'parent_name' => $p->post_parent ? get_post( $p->post_parent )->post_name : 'none',
		);
	}
}

echo "Total pages: " . count( $pages ) . "\n";
echo "Mismatched permalink vs route: " . count( $mismatches ) . "\n\n";

foreach ( $mismatches as $m ) {
	echo "[ID: {$m['id']}] Permalink: '{$m['permalink']}' vs Route: '{$m['route']}' | Parent: {$m['parent_name']}\n";
}
