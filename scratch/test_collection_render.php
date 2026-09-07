<?php
require_once __DIR__ . '/../../../../wp-load.php';

$file = get_template_directory() . '/shop-us/collections/vive-flow/index.htm';
$html = file_get_contents($file);

$processed = theme_vive_products_prepare_html($html, $file);

// Check if image tags have valid src and if there are duplicate buttons
preg_match_all('#<div class="product-item"[\s\S]*?</div>\s*</div>#i', $processed, $matches);
echo "Total cards found: " . count($matches[0]) . PHP_EOL;
if (!empty($matches[0])) {
    echo "First card sample:\n" . substr($matches[0][0], 0, 1000) . PHP_EOL;
}
