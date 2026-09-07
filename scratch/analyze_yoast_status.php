<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY post_type, ID ASC");

$front_page_id = (int) get_option('page_on_front');

echo "Front page ID: " . $front_page_id . PHP_EOL;

$counts = ['page' => 0, 'post' => 0, 'product' => 0];
$missing_kw = 0;
$missing_title = 0;
$missing_desc = 0;
$ellipsis_desc = 0;

foreach ($posts as $p) {
    $counts[$p->post_type] = ($counts[$p->post_type] ?? 0) + 1;
    $kw = get_post_meta($p->ID, '_yoast_wpseo_focuskw', true);
    $title = get_post_meta($p->ID, '_yoast_wpseo_title', true);
    $desc = get_post_meta($p->ID, '_yoast_wpseo_metadesc', true);
    
    if (empty($kw)) $missing_kw++;
    if (empty($title)) $missing_title++;
    if (empty($desc)) $missing_desc++;
    if (strpos($desc, '...') !== false || strpos($desc, '…') !== false) $ellipsis_desc++;
}

echo "Counts by type: " . json_encode($counts) . PHP_EOL;
echo "Missing Keyphrase: {$missing_kw}" . PHP_EOL;
echo "Missing Title: {$missing_title}" . PHP_EOL;
echo "Missing Meta Description: {$missing_desc}" . PHP_EOL;
echo "Description with ellipsis: {$ellipsis_desc}" . PHP_EOL;
