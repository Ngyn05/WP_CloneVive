<?php
define( 'WP_USE_THEMES', false );
require dirname( __DIR__, 4 ) . '/wp-load.php';

$p = get_page_by_path( 'newsroom', OBJECT, 'page' );
echo "Page 'newsroom': " . var_export( $p ? $p->ID : false, true ) . "\n";

$newsroom_pages = get_posts([
    'post_type' => 'page',
    'meta_key' => '_theme_vive_mirror_route',
    'meta_value' => '/newsroom/',
]);
echo "Meta '_theme_vive_mirror_route' = '/newsroom/': " . count($newsroom_pages) . "\n";
