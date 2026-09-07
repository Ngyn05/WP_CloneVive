<?php
require_once dirname(__DIR__) . '/inc/translation-cms.php';
require_once dirname(__DIR__) . '/inc/product-cms.php';

$file = dirname(__DIR__) . '/shop-us/products/vive-eagle/index.htm';
$raw = file_get_contents($file);
$processed = theme_vive_products_prepare_html($raw, $file);

file_put_contents(__DIR__ . '/processed.html', $processed);
echo "Wrote processed.html\n";
