<?php
define('ABSPATH', 'C:/Users/hnguy/Local Sites/vivevn/app/public/');
function add_action($tag, $callback, $priority = 10, $accepted_args = 1){}
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1){}
function add_shortcode($tag, $callback){}
function esc_url($u){return $u;}
function esc_attr($a){return htmlspecialchars((string)$a, ENT_QUOTES);}
function esc_html($h){return htmlspecialchars((string)$h, ENT_QUOTES);}
function admin_url($p){return '/wp-admin/'.$p;}
function wp_create_nonce($a){return 'dummy_nonce';}
function home_url($p=''){return 'http://vivevn.local'.$p;}
function get_template_directory(){return 'C:/Users/hnguy/Local Sites/vivevn/app/public/wp-content/themes/theme_vive';}
function get_template_directory_uri(){return 'http://vivevn.local/wp-content/themes/theme_vive';}
function get_option($k, $d=false){return $d;}
function wp_parse_url($url, $component = -1){return parse_url($url, $component);}
function wp_json_encode($d){return json_encode($d);}

require_once 'c:/Users/hnguy/Local Sites/vivevn/app/public/wp-content/themes/theme_vive/inc/product-cms.php';

$file = 'c:/Users/hnguy/Local Sites/vivevn/app/public/wp-content/themes/theme_vive/shop-us/products/vive-eagle/index.htm';
$html = file_get_contents($file);

$res = theme_vive_products_prepare_html($html, $file);
echo "Result length: " . strlen((string)$res) . "\n";
echo "Has Zalo? " . (strpos($res, 'TƯ VẤN QUA ZALO') !== false ? 'YES' : 'NO') . "\n";
echo "Has Mua ngay? " . (strpos($res, 'data-theme-vive-buy-mode="popup"') !== false ? 'YES' : 'NO') . "\n";
echo "Has RestockRocket? " . (strpos($res, '_RestockRocketConfig') !== false ? 'YES' : 'NO') . "\n";
echo "Has tryon? " . (strpos($res, 'tryon__cta') !== false ? 'YES' : 'NO') . "\n";
