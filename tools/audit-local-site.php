<?php
/** Read-only local pre-launch audit for the VIVE site. */

if ( PHP_SAPI !== 'cli' ) {
	exit( "CLI only.\n" );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_file( $wp_load ) ) {
	exit( "Could not locate wp-load.php.\n" );
}
require_once $wp_load;

global $wpdb;

$post_types = array( 'page', 'post', 'product' );
$counts     = array();
foreach ( $post_types as $post_type ) {
	$counts[ $post_type ] = (int) wp_count_posts( $post_type )->publish;
}

$meta_keys = array(
	'_yoast_wpseo_focuskw',
	'_yoast_wpseo_title',
	'_yoast_wpseo_metadesc',
	'_yoast_wpseo_linkdex',
	'_yoast_wpseo_content_score',
);
$meta_counts = array();
foreach ( $meta_keys as $meta_key ) {
	$meta_counts[ $meta_key ] = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT pm.post_id)
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
			AND pm.meta_value <> ''
			AND p.post_status = 'publish'
			AND p.post_type IN ('page','post','product')",
			$meta_key
		)
	);
}

$indexables = $wpdb->get_results(
	"SELECT object_sub_type AS post_type,
		COUNT(*) AS total,
		SUM(CASE WHEN primary_focus_keyword <> '' THEN 1 ELSE 0 END) AS with_keyword,
		SUM(CASE WHEN primary_focus_keyword_score BETWEEN 41 AND 70 THEN 1 ELSE 0 END) AS seo_ok,
		SUM(CASE WHEN readability_score BETWEEN 41 AND 70 THEN 1 ELSE 0 END) AS readability_ok,
		SUM(CASE WHEN is_robots_noindex = 1 THEN 1 ELSE 0 END) AS noindex_count
	FROM {$wpdb->prefix}yoast_indexable
	WHERE object_type = 'post'
	AND object_sub_type IN ('page','post','product')
	GROUP BY object_sub_type",
	ARRAY_A
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

$duplicate_titles = $wpdb->get_results(
	"SELECT pm.meta_value AS value, COUNT(*) AS total
	FROM {$wpdb->postmeta} pm
	INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	WHERE pm.meta_key = '_yoast_wpseo_title'
	AND p.post_status = 'publish'
	AND p.post_type IN ('page','post','product')
	GROUP BY pm.meta_value HAVING COUNT(*) > 1
	ORDER BY total DESC LIMIT 20",
	ARRAY_A
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

$duplicate_descriptions = $wpdb->get_results(
	"SELECT pm.meta_value AS value, COUNT(*) AS total
	FROM {$wpdb->postmeta} pm
	INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	WHERE pm.meta_key = '_yoast_wpseo_metadesc'
	AND p.post_status = 'publish'
	AND p.post_type IN ('page','post','product')
	GROUP BY pm.meta_value HAVING COUNT(*) > 1
	ORDER BY total DESC LIMIT 20",
	ARRAY_A
); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

$active_plugins = (array) get_option( 'active_plugins', array() );
$woocommerce_pages = array();
foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $page_name ) {
	$page_id = (int) get_option( 'woocommerce_' . $page_name . '_page_id' );
	$woocommerce_pages[ $page_name ] = array(
		'id'  => $page_id,
		'url' => $page_id ? get_permalink( $page_id ) : '',
	);
}
$report         = array(
	'home_url'               => home_url( '/' ),
	'site_url'               => site_url( '/' ),
	'permalink_structure'     => get_option( 'permalink_structure' ),
	'blog_public'             => (string) get_option( 'blog_public' ),
	'environment'             => wp_get_environment_type(),
	'wp_debug'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
	'front_page_id'           => (int) get_option( 'page_on_front' ),
	'published_counts'        => $counts,
	'yoast_meta_counts'       => $meta_counts,
	'yoast_indexables'        => $indexables,
	'duplicate_titles'        => $duplicate_titles,
	'duplicate_descriptions'  => $duplicate_descriptions,
	'active_plugins'          => $active_plugins,
	'woocommerce_pages'       => $woocommerce_pages,
	'featured_image_count'    => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id'" ),
	'blog_cover_count'        => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_vive_blog_cover_url' AND meta_value NOT IN ('', 'N/A')" ),
);

echo wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
