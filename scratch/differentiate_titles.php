<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$pages = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_status='publish' AND post_type='page' ORDER BY ID ASC");

foreach ($pages as $p) {
    $route = get_post_meta($p->ID, '_theme_vive_mirror_route', true);
    if (!$route) $route = '/' . $p->post_name . '/';
    
    $new_title = $p->post_title;
    
    // Phân biệt theo route
    if (strpos($route, 'vive-lbss/category_howto') !== false) {
        $new_title = "Hướng dẫn & Cẩm nang VIVE LBE Tracking";
    } elseif (strpos($route, 'business-plus/category_howto') !== false) {
        $new_title = "Hướng dẫn & Cẩm nang VIVE Business+";
    } elseif (strpos($route, 'vive-focus-vision/vr-setup') !== false) {
        $new_title = "Cài đặt VR cho VIVE Focus Vision";
    } elseif (strpos($route, 'vive-xr/vr-setup') !== false) {
        $new_title = "Cài đặt VR cho VIVE XR Elite";
    } elseif (strpos($route, 'business/support/dms') !== false) {
        $new_title = "Hỗ trợ Quản trị thiết bị DMS Doanh nghiệp";
    } elseif (strpos($route, 'business/dms') !== false) {
        $new_title = "Giải pháp Quản trị thiết bị DMS";
    } elseif (strpos($route, 'hub/en-us/download') !== false) {
        $new_title = "Tải xuống Phần mềm VIVE Hub (Global)";
    } elseif (strpos($route, 'vive-hub/download') !== false) {
        $new_title = "Tải xuống Phần mềm VIVE Hub";
    } elseif (strpos($route, 'enterprise/product') !== false) {
        $new_title = "Danh mục Sản phẩm Doanh nghiệp VIVE Enterprise";
    } elseif (strpos($route, 'business/product') !== false) {
        $new_title = "Danh mục Sản phẩm Giải pháp Doanh nghiệp";
    } elseif (strpos($route, 'business/mea-en/support') !== false) {
        $new_title = "Trung tâm Hỗ trợ Doanh nghiệp VIVE MEA";
    } elseif (strpos($route, 'business/support') !== false) {
        $new_title = "Trung tâm Hỗ trợ Khách hàng Doanh nghiệp";
    }
    
    if ($new_title !== $p->post_title) {
        $wpdb->update($wpdb->posts, ['post_title' => $new_title], ['ID' => $p->ID]);
        echo "Updated ID {$p->ID}: '{$p->post_title}' -> '{$new_title}'\n";
    }
}

// Xử lý các bài newsroom trùng tiêu đề
$news = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_status='publish' AND post_title LIKE 'Tin tức HTC VIVE (%' ORDER BY post_name ASC");
$news_groups = [];
foreach ($news as $n) {
    $news_groups[$n->post_title][] = $n;
}
foreach ($news_groups as $title => $items) {
    if (count($items) > 1) {
        $idx = 1;
        foreach ($items as $item) {
            $updated_news_title = preg_replace('/\)\s*$/', " - Phần {$idx})", $title);
            $wpdb->update($wpdb->posts, ['post_title' => $updated_news_title], ['ID' => $item->ID]);
            echo "Updated News ID {$item->ID}: '{$title}' -> '{$updated_news_title}'\n";
            $idx++;
        }
    }
}
