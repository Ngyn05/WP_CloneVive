<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

// Lay tat ca pages trong database
$pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$data = [];
$non_vietnamese = [];

foreach ($pages as $p) {
    $permalink = get_permalink($p);
    $path = wp_make_link_relative($permalink);
    $title = $p->post_title;
    
    // Check if title looks purely English without Vietnamese accents for common words
    // We will save to check
    $data[] = [
        'id' => $p->ID,
        'title' => $title,
        'slug' => $p->post_name,
        'path' => $path,
        'permalink' => $permalink,
        'parent' => $p->post_parent,
    ];
}

file_put_contents(
    get_template_directory() . '/scratch/all_db_pages.json',
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "Total published pages in DB: " . count($data) . "\n";
