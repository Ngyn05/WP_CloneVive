<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

foreach([2318, 2337, 2338] as $id){ 
    echo "ID {$id}: " . get_the_title($id) . PHP_EOL;
    echo "  KW: " . get_post_meta($id, '_yoast_wpseo_focuskw', true) . PHP_EOL;
    echo "  SEO Title: " . get_post_meta($id, '_yoast_wpseo_title', true) . PHP_EOL;
    echo "  Desc: " . get_post_meta($id, '_yoast_wpseo_metadesc', true) . PHP_EOL;
    $idx = $wpdb->get_row("SELECT title, description, primary_focus_keyword FROM {$wpdb->prefix}yoast_indexable WHERE object_type='post' AND object_id={$id}");
    if ($idx) {
        echo "  Indexable Title: {$idx->title}\n";
        echo "  Indexable KW: {$idx->primary_focus_keyword}\n";
        echo "  Indexable Desc: {$idx->description}\n";
    }
}
