<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

$newsroom_page = get_page_by_path('newsroom');
if (!$newsroom_page) {
    $parent_id = wp_insert_post([
        'post_title' => 'Phòng báo chí & Tin tức VIVE',
        'post_name' => 'newsroom',
        'post_type' => 'page',
        'post_status' => 'publish',
    ]);
} else {
    $parent_id = $newsroom_page->ID;
    wp_update_post([
        'ID' => $parent_id,
        'post_title' => 'Phòng báo chí & Tin tức VIVE',
    ]);
}

$years = ['2025', '2024', '2023', '2021', '2020', '2019', '2018', '2017', '2016', '2015'];
foreach ($years as $yr) {
    $existing = get_page_by_path('newsroom/' . $yr);
    if (!$existing) {
        $id = wp_insert_post([
            'post_title' => 'Thông cáo báo chí VIVE ' . $yr,
            'post_name' => $yr,
            'post_parent' => $parent_id,
            'post_type' => 'page',
            'post_status' => 'publish',
        ]);
        echo "Inserted newsroom/{$yr} with ID {$id}\n";
    } else {
        wp_update_post([
            'ID' => $existing->ID,
            'post_title' => 'Thông cáo báo chí VIVE ' . $yr,
            'post_parent' => $parent_id,
        ]);
        echo "Updated newsroom/{$yr} with ID {$existing->ID}\n";
    }
}

echo "Done newsroom sync.\n";
