<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

// 1. Xóa các trang trùng lặp suffix -2
$duplicate_slugs = [
    'contact-2',
    '2025-2', '2024-2', '2023-2', '2021-2', '2020-2',
    '2019-2', '2018-2', '2017-2', '2016-2', '2015-2'
];

foreach ($duplicate_slugs as $slug) {
    $dup_posts = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'name' => $slug,
        'numberposts' => -1,
    ]);
    foreach ($dup_posts as $dp) {
        wp_delete_post($dp->ID, true);
        echo "Deleted duplicate page ID {$dp->ID} (slug: {$slug})\n";
    }
}

// 2. Dọn dẹp các trang slug thừa /home, /us của subdomains nếu không cần thiết
$redundant_paths = [
    'arts/us', 'business/us', 'business/home', 'developer/us', 'developer/home',
    'enterprise/us', 'enterprise/home', 'vr4impact/home', 'sync/home',
    'locator/home', 'roundtable/home', 'vivex/us'
];

foreach ($redundant_paths as $rpath) {
    $p = get_page_by_path($rpath);
    if ($p) {
        wp_delete_post($p->ID, true);
        echo "Deleted redundant subdomain page ID {$p->ID} (path: {$rpath})\n";
    }
}

echo "Database cleanup completed.\n";
