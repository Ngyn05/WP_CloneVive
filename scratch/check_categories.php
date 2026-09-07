<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

echo "=== WOOCOMMERCE CATEGORIES ===\n";
$terms = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
]);
if (!is_wp_error($terms)) {
    foreach ($terms as $t) {
        echo "- {$t->name} (slug: {$t->slug}, count: {$t->count})\n";
    }
} else {
    echo "Error: " . $terms->get_error_message() . "\n";
}

echo "\n=== RENDER /product/ OUTPUT TABS ===\n";
$file = theme_vive_page_file('vive.com', '/product/');
echo "File matched: " . ($file ?: 'none') . "\n";
if ($file) {
    $rendered = theme_vive_render($file);
    if (preg_match('#<div class="productTags-list">([\s\S]*?)</div>#i', $rendered, $m)) {
        echo "productTags-list content:\n" . trim($m[1]) . "\n";
    }
}
