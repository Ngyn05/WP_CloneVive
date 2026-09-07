<?php
$wp_load = 'C:/Users/hnguy/Local Sites/vivevn/app/public/wp-load.php';
if (file_exists($wp_load)) {
    require_once $wp_load;
}

$file = get_template_directory() . '/shop-us/products/vive-eagle/index.htm';
echo "Testing file: " . $file . "\n";
echo "Exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";

$res = theme_vive_render($file);
echo "Result length: " . strlen($res) . "\n";
echo "Empty: " . (empty($res) ? 'YES' : 'NO') . "\n";

// Now check if theme_vive_prepare_fallback_html has product preparation:
$fallback = file_get_contents($file);
$fallback_res = theme_vive_prepare_fallback_html($fallback, $file);
echo "Fallback res length: " . strlen($fallback_res) . "\n";
echo "Has product-order.js in fallback? " . (strpos($fallback_res, 'product-order.js') !== false ? 'YES' : 'NO') . "\n";
echo "Has product-order.js in render res? " . (strpos($res, 'product-order.js') !== false ? 'YES' : 'NO') . "\n";
