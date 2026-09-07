<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$pages = $wpdb->get_results("SELECT ID, post_title, post_name, post_type, post_content FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY post_type, ID ASC");

echo "Total items: " . count($pages) . PHP_EOL;

$yoast_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}yoast_indexable'");
echo "Yoast indexable table: " . ($yoast_table ? 'YES' : 'NO') . PHP_EOL;

$sample = array_slice($pages, 0, 10);
foreach ($sample as $p) {
    $meta_title = get_post_meta($p->ID, '_yoast_wpseo_title', true);
    $meta_desc = get_post_meta($p->ID, '_yoast_wpseo_metadesc', true);
    $meta_kw = get_post_meta($p->ID, '_yoast_wpseo_focuskw', true);
    echo "ID: {$p->ID} | Type: {$p->post_type} | Title: {$p->post_title} | KW: {$meta_kw} | SEO Title: {$meta_title} | Desc: " . mb_substr($meta_desc, 0, 30) . PHP_EOL;
}
