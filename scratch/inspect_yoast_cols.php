<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$columns = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}yoast_indexable");
foreach ($columns as $col) {
    echo "{$col->Field} ({$col->Type})" . PHP_EOL;
}
