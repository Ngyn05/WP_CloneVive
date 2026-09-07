<?php
/** Mark public records that do not resolve to HTTP 200 for Yoast exclusion. */

if ( PHP_SAPI !== 'cli' ) { exit( "CLI only.\n" ); }
$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_file( $wp_load ) ) { exit( "Could not locate wp-load.php.\n" ); }
require_once $wp_load;

$execute = in_array( '--execute', $argv, true );
$stdin   = in_array( '--stdin', $argv, true );
$posts   = get_posts( array(
	'post_type'      => array( 'page', 'post', 'product' ),
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => 'ID',
	'order'          => 'ASC',
) );

if ( $stdin ) {
	$raw  = ltrim( stream_get_contents( STDIN ), "\xEF\xBB\xBF" );
	$urls = json_decode( $raw, true );
	if ( ! is_array( $urls ) ) { exit( "Expected a JSON URL array on STDIN.\n" ); }
	if ( $execute ) { delete_post_meta_by_key( '_theme_vive_sitemap_exclude' ); }
	$marked = 0;
	$missed = 0;
	foreach ( array_unique( $urls ) as $url ) {
		$post_id = url_to_postid( $url );
		if ( ! $post_id ) {
			$missed++;
			printf( "NON-POST URL | %s\n", $url );
			continue;
		}
		$marked++;
		if ( $execute ) { update_post_meta( $post_id, '_theme_vive_sitemap_exclude', '1' ); }
		printf( "%s %d | %s\n", $execute ? 'EXCLUDED' : 'WOULD EXCLUDE', $post_id, $url );
	}
	printf( "COMPLETE: marked=%d non_post=%d total=%d mode=%s\n", $marked, $missed, count( $urls ), $execute ? 'execute' : 'preview' );
	exit;
}

$included = 0;
$excluded = 0;
$errors   = 0;
foreach ( $posts as $post ) {
	$url      = get_permalink( $post );
	$response = wp_remote_get( $url, array(
		'timeout'     => 30,
		'redirection' => 0,
		'headers'     => array( 'User-Agent' => 'VIVE-Local-Sitemap-Audit/1.0' ),
	) );
	if ( is_wp_error( $response ) ) {
		$errors++;
		printf( "ERROR %d | %s | %s\n", $post->ID, $url, $response->get_error_message() );
		continue;
	}
	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 === $status ) {
		$included++;
		if ( $execute ) { delete_post_meta( $post->ID, '_theme_vive_sitemap_exclude' ); }
		continue;
	}
	$excluded++;
	if ( $execute ) { update_post_meta( $post->ID, '_theme_vive_sitemap_exclude', '1' ); }
	printf( "%s %d | HTTP %d | %s\n", $execute ? 'EXCLUDED' : 'WOULD EXCLUDE', $post->ID, $status, $url );
}

printf( "COMPLETE: included=%d excluded=%d errors=%d total=%d mode=%s\n", $included, $excluded, $errors, count( $posts ), $execute ? 'execute' : 'preview' );
