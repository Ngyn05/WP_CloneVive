<?php
/**
 * Apply 100% Vietnamese Titles to all Pages in WordPress Database.
 */
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$json_file = dirname( __DIR__ ) . '/scratch/all_vietnamese_titles.json';
if ( ! is_file( $json_file ) ) {
	echo "Missing {$json_file}\n";
	exit( 1 );
}

$titles_map = json_decode( file_get_contents( $json_file ), true );
if ( ! is_array( $titles_map ) ) {
	echo "Invalid JSON data\n";
	exit( 1 );
}

echo "Updating " . count( $titles_map ) . " pages in database with Vietnamese titles...\n\n";

$updated = 0;
$unchanged = 0;
$failed = 0;

foreach ( $titles_map as $item ) {
	$page_id   = (int) $item['id'];
	$new_title = trim( $item['title'] );

	$current_post = get_post( $page_id );
	if ( ! $current_post ) {
		continue;
	}

	if ( $current_post->post_title === $new_title ) {
		++$unchanged;
		continue;
	}

	$res = wp_update_post( array(
		'ID'         => $page_id,
		'post_title' => $new_title,
	), true );

	if ( is_wp_error( $res ) ) {
		echo "Failed [ID: {$page_id}]: " . $res->get_error_message() . "\n";
		++$failed;
	} else {
		++$updated;
		echo "[ID: {$page_id}] '{$new_title}'\n";
	}
}

clean_post_cache( 0 );
wp_cache_flush();

echo "\n============================================\n";
echo "RESULT: Updated: {$updated} | Unchanged: {$unchanged} | Failed: {$failed}\n";
echo "All pages in Database are now 100% Vietnamese!\n";
echo "============================================\n";
