<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$pages = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_title LIKE '%Doanh nghiệp%' OR post_title LIKE '%Solutions%' LIMIT 10");

foreach ($pages as $p) {
    $kw = get_post_meta($p->ID, '_yoast_wpseo_focuskw', true);
    $title = get_post_meta($p->ID, '_yoast_wpseo_title', true);
    $desc = get_post_meta($p->ID, '_yoast_wpseo_metadesc', true);
    echo "ID: {$p->ID} | {$p->post_title}\n";
    echo "  KW: {$kw}\n";
    echo "  SEO Title: {$title}\n";
    echo "  Meta Desc (" . mb_strlen($desc) . " chars): {$desc}\n\n";
}
