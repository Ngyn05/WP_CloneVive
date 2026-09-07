<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';
$posts = get_posts([
    'name' => '2025-04-01',
    'post_type' => 'any',
    'post_status' => 'any'
]);
$data = [];
foreach ($posts as $p) {
    $data[] = [
        'id' => $p->ID,
        'title' => $p->post_title,
        'slug' => $p->post_name,
        'parent' => $p->post_parent,
        'parent_slug' => $p->post_parent ? get_post($p->post_parent)->post_name : '',
        'route' => get_post_meta($p->ID, '_theme_vive_mirror_route', true),
        'file' => get_post_meta($p->ID, '_theme_vive_mirror_file', true),
        'permalink' => get_permalink($p->ID),
    ];
}
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
