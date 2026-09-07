<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

$pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'numberposts' => -1,
]);

$unresolved = [];
$resolved = [];

foreach ($pages as $p) {
    $permalink = get_permalink($p);
    $path = wp_make_link_relative($permalink);
    $clean_path = theme_vive_clean_slug_path($path);
    
    // 1. Kiểm tra router logic
    $file = false;
    $is_redirect = false;
    
    // Redirects
    if (preg_match('#^/product/(?<prod>vive-focus-vision|vive-pro2|vive-pro2-full-kit|vive-flow|vive-xr-elite|vive-eagle)/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/(?:tai-khoan|thanh-toan|gio-hang|cua-hang)/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/newsroom/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/solution/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/ready/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/rental/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (preg_match('#^/vive-hub/?$#i', $clean_path)) {
        $is_redirect = true;
    } elseif (0 === strpos($clean_path, '/shop/')) {
        $path_part = substr($clean_path, 5);
        $file = theme_vive_page_file('shop-us.vive.com', $path_part ? $path_part : '/');
    } elseif (0 === strpos($clean_path, '/blog/')) {
        $file = theme_vive_page_file('blog.vive.com', substr($clean_path, 6));
    } elseif (preg_match('#^/(?<slug>business|developer|enterprise|service|blog|mars|sync|viverse|arts|locator|roundtable|vivex|vr4impact|hub|store)(?<path>/.*)?$#i', $clean_path, $m)) {
        $host = theme_vive_slug_host($m['slug']);
        $sub_path = !empty($m['path']) ? $m['path'] : '/';
        $file = theme_vive_page_file($host, $sub_path);
    } else {
        $file = theme_vive_page_file('vive.com', $clean_path);
        if (!$file) {
            $file = theme_vive_page_file('blog.vive.com', $clean_path);
        }
    }
    
    if ($file || $is_redirect) {
        $resolved[] = [
            'id' => $p->ID,
            'title' => $p->post_title,
            'path' => $path,
            'file' => $file ? str_replace(get_template_directory(), '', $file) : 'REDIRECT_301'
        ];
    } else {
        $unresolved[] = [
            'id' => $p->ID,
            'title' => $p->post_title,
            'path' => $path,
            'clean_path' => $clean_path
        ];
    }
}

echo "=== KET QUA KIEM TRA TOAN BO " . count($pages) . " TRANG DATABASE ===\n";
echo "Resolved (co template/redirect): " . count($resolved) . "\n";
echo "Unresolved: " . count($unresolved) . "\n";

if (!empty($unresolved)) {
    foreach ($unresolved as $u) {
        echo "FAIL: ID {$u['id']} | Path: {$u['path']} | Title: {$u['title']}\n";
    }
} else {
    echo "\n>>> 100% TAT CA CAC TRANG TRONG DATABASE DEU HOP LE VA SAN SANG RENDER (STATUS 200/REDIRECT) <<<\n";
}
