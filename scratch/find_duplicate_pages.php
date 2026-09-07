<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$pages = $wpdb->get_results("SELECT ID, post_title, post_name, post_status, post_parent, guid FROM {$wpdb->posts} WHERE post_status='publish' AND post_type='page' ORDER BY post_title ASC");

$title_groups = [];
foreach ($pages as $p) {
    $route = get_post_meta($p->ID, '_theme_vive_mirror_route', true);
    $title_groups[$p->post_title][] = [
        'ID' => $p->ID,
        'slug' => $p->post_name,
        'route' => $route,
        'permalink' => get_permalink($p->ID)
    ];
}

$duplicates = [];
foreach ($title_groups as $title => $items) {
    if (count($items) > 1) {
        $duplicates[$title] = $items;
    }
}

echo "Found " . count($duplicates) . " duplicate page title groups:\n\n";

foreach ($duplicates as $title => $items) {
    echo "Title: [{$title}] (" . count($items) . " pages)\n";
    foreach ($items as $it) {
        echo "  - ID {$it['ID']} | slug: {$it['slug']} | route: {$it['route']} | link: {$it['permalink']}\n";
    }
    echo "\n";
}
