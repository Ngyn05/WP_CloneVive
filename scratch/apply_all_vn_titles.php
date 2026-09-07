<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

$json_file = get_template_directory() . '/scratch/title_updates.json';
if (!file_exists($json_file)) {
    die("File not found\n");
}

$updates = json_decode(file_get_contents($json_file), true);
$count = 0;

foreach ($updates as $item) {
    $id = intval($item['id']);
    $title = $item['new_title'];
    
    wp_update_post([
        'ID' => $id,
        'post_title' => $title,
    ]);
    $count++;
}

echo "Successfully updated {$count} page titles in WordPress database!\n";
