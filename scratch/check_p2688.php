<?php
define('WP_USE_THEMES', false);
require_once __DIR__ . '/../../../../wp-load.php';

$p = wc_get_product(2688);
if ($p) {
    echo "ID: " . $p->get_id() . "\n";
    echo "Name: " . $p->get_name() . "\n";
    echo "Slug: " . $p->get_slug() . "\n";
    echo "Status: " . $p->get_status() . "\n";
    echo "Meta THEME_VIVE_PRODUCT_META: " . get_post_meta($p->get_id(), THEME_VIVE_PRODUCT_META, true) . "\n";
} else {
    echo "Product 2688 not found\n";
    $all = get_posts(array('post_type' => 'product', 'numberposts' => 10, 'post_status' => 'any'));
    foreach ($all as $a) {
        echo " - " . $a->ID . " | " . $a->post_name . " | " . $a->post_title . "\n";
    }
}
