<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$posts = $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY ID ASC");

$clean_map = [
    'Category_Howto' => 'Hướng dẫn & Cẩm nang',
    'Vive Lbss' => 'VIVE LBE Định vị không gian',
    'Business Plus' => 'VIVE Business Plus',
    'Solutions' => 'Giải pháp Thực tế ảo Doanh nghiệp',
    'Accessory' => 'Phụ kiện Kính thực tế ảo VIVE',
    'Legal' => 'Điều khoản Pháp lý & Chính sách',
    'Cookies' => 'Chính sách Cookie & Quyền riêng tư',
    'Cua Hang' => 'Cửa hàng Sản phẩm VIVE',
    'Gio Hang' => 'Giỏ hàng VIVE',
    'Thanh Toan' => 'Thanh toán Đơn hàng',
    'Tai Khoan' => 'Tài khoản Thành viên VIVE',
];

$renamed = 0;
foreach ($posts as $p) {
    $new_title = $p->post_title;
    foreach ($clean_map as $search => $replace) {
        if (stripos($new_title, $search) !== false) {
            $new_title = str_ireplace($search, $replace, $new_title);
        }
    }
    $new_title = str_replace('_', ' ', $new_title);
    $new_title = preg_replace('/\s+/u', ' ', trim($new_title));
    
    if ($new_title !== $p->post_title) {
        $wpdb->update($wpdb->posts, ['post_title' => $new_title], ['ID' => $p->ID]);
        $renamed++;
    }
}

echo "Renamed {$renamed} titles for better readability!\n";
