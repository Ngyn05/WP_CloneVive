<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$pages = $wpdb->get_results("SELECT ID, post_title, post_name, post_status FROM {$wpdb->posts} WHERE post_status='publish' AND post_type='page' ORDER BY ID ASC");

$route_map = [];
$duplicates_to_trash = [];

foreach ($pages as $p) {
    $route = get_post_meta($p->ID, '_theme_vive_mirror_route', true);
    if (!$route) {
        $route = '/' . $p->post_name . '/';
    }
    // Normalize route
    $clean_route = '/' . trim(preg_replace('#^/(?:business|enterprise)/us/#i', '/business/', $route), '/') . '/';
    $clean_route = preg_replace('#^/us/#i', '/', $clean_route);
    
    if (isset($route_map[$clean_route])) {
        // Đã có trang khác cùng route -> trang này là duplicate
        $duplicates_to_trash[] = [
            'trash_id' => $p->ID,
            'keep_id' => $route_map[$clean_route],
            'route' => $clean_route,
            'title' => $p->post_title
        ];
    } else {
        $route_map[$clean_route] = $p->ID;
    }
}

echo "Found " . count($duplicates_to_trash) . " exact same route duplicates to delete:\n";
foreach ($duplicates_to_trash as $d) {
    echo "  - Trash ID {$d['trash_id']} (keeping ID {$d['keep_id']}) for route {$d['route']} - Title: {$d['title']}\n";
    wp_delete_post($d['trash_id'], true); // Delete permanently
    // Delete from Yoast indexable
    $wpdb->delete("{$wpdb->prefix}yoast_indexable", ['object_type' => 'post', 'object_id' => $d['trash_id']]);
}

echo "\nCleaned all exact duplicate route pages!\n";
