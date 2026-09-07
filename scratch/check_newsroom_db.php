<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

$p = get_page_by_path('newsroom');
if ($p) {
    echo "Page ID: " . $p->ID . " | Title: " . $p->post_title . " | Permalink: " . get_permalink($p) . "\n";
} else {
    echo "Not found newsroom in DB\n";
}

$p25 = get_page_by_path('newsroom/2025');
if ($p25) {
    echo "2025 Page ID: " . $p25->ID . " | Title: " . $p25->post_title . " | Permalink: " . get_permalink($p25) . "\n";
} else {
    echo "Not found newsroom/2025 in DB\n";
}
