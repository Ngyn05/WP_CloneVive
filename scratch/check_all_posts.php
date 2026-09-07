<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$res = $wpdb->get_results("SELECT post_type, post_status, count(*) as c FROM {$wpdb->posts} GROUP BY post_type, post_status");
foreach ($res as $r) {
    echo "Type: {$r->post_type} | Status: {$r->post_status} | Count: {$r->c}\n";
}

// Kiểm tra xem trang 'Doanh nghiệp: Category_Howto' có ID là bao nhiêu
$items = $wpdb->get_results("SELECT ID, post_title, post_type, post_status FROM {$wpdb->posts} WHERE post_title LIKE '%Category_Howto%' OR post_title LIKE '%Vive Lbss%'");
foreach ($items as $it) {
    echo "Found: ID {$it->ID} | Status: {$it->post_status} | Type: {$it->post_type} | Title: {$it->post_title}\n";
}
