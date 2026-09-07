<?php
require_once __DIR__ . '/../../../../wp-load.php';

$all_prods = wc_get_products(['limit' => 20, 'orderby' => 'date', 'order' => 'DESC']);
echo "Recent products in DB:\n";
foreach ($all_prods as $p) {
    echo "- ID: {$p->get_id()} | Name: {$p->get_name()} | Slug: {$p->get_slug()} | Meta: " . get_post_meta($p->get_id(), THEME_VIVE_PRODUCT_META, true) . "\n";
}
