<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

// 1. Kiểm tra 345 Pages trong Database
$pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1,
]);

$db_report = [];
$db_fail = [];

foreach ($pages as $p) {
    $permalink = get_permalink($p);
    $path = wp_make_link_relative($permalink);
    $clean_path = theme_vive_clean_slug_path($path);
    
    // Test find page file
    $file = theme_vive_page_file('vive.com', $clean_path);
    if (!$file) {
        $file = theme_vive_page_file('blog.vive.com', $clean_path);
    }
    if (!$file) {
        $file = theme_vive_page_file('shop-us.vive.com', $clean_path);
    }
    
    // Check if it matches a WP post or custom route
    $status = ($file && file_exists($file)) ? '200_FOUND' : 'CHECK_ROUTE';
    
    $item = [
        'id' => $p->ID,
        'title' => $p->post_title,
        'path' => $path,
        'clean_path' => $clean_path,
        'file' => $file ? str_replace(get_template_directory(), '', $file) : null,
        'status' => $status
    ];
    
    $db_report[] = $item;
    if ($status !== '200_FOUND') {
        $db_fail[] = $item;
    }
}

file_put_contents(
    get_template_directory() . '/scratch/internal_db_pages_check.json',
    json_encode([
        'total_pages' => count($pages),
        'found_count' => count($pages) - count($db_fail),
        'unresolved_count' => count($db_fail),
        'unresolved' => $db_fail,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "--- KET QUA KIEM TRA NOI BO 345 TRANG DATABASE ---\n";
echo "Tong so trang trong DB: " . count($pages) . "\n";
echo "So trang tim thay file template/noi dung: " . (count($pages) - count($db_fail)) . "\n";
echo "So trang chua phan giai truc tiep duoc: " . count($db_fail) . "\n";

if (!empty($db_fail)) {
    foreach ($db_fail as $f) {
        echo "Unresolved: ID {$f['id']} | Path: {$f['path']} | Title: {$f['title']}\n";
    }
}
