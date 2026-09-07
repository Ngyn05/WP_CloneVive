<?php
define('WP_USE_THEMES', false);
require_once('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php');

$posts = get_posts([
    'post_type' => ['page', 'post'],
    'numberposts' => -1,
    'post_status' => 'publish'
]);

echo "Searching for posts with Dongle or similar:\n";
foreach ($posts as $p) {
    if (stripos($p->post_title, 'dongle') !== false || stripos(get_post_meta($p->ID, '_yoast_wpseo_title', true), 'dongle') !== false) {
        $seo_title = get_post_meta($p->ID, '_yoast_wpseo_title', true);
        echo "ID: {$p->ID} | Title: {$p->post_title} | Slug: {$p->post_name}\nSEO Title: {$seo_title}\n\n";
    }
}
