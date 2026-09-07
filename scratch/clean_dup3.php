<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

// Xóa các trang trùng lặp suffix -3
$dup3 = [
    '2025-3', '2024-3', '2023-3', '2021-3', '2020-3',
    '2019-3', '2018-3', '2017-3', '2016-3', '2015-3'
];

foreach ($dup3 as $slug) {
    $dup_posts = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'name' => $slug,
        'numberposts' => -1,
    ]);
    foreach ($dup_posts as $dp) {
        wp_delete_post($dp->ID, true);
        echo "Deleted -3 duplicate page ID {$dp->ID} (slug: {$slug})\n";
    }
}

echo "Done cleanup -3 duplicates.\n";
