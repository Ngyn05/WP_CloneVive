<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$specific_titles = [
    1598 => "Tải ứng dụng VIVE Business+ Console",
    1596 => "Thêm thiết bị vào nhóm VIVE Business+",
    1597 => "Tạo và quản lý nhóm thiết bị VIVE Business+",
    1599 => "Phiên bản phần mềm và ứng dụng yêu cầu VIVE Business+",
    1600 => "Cài đặt Tracker sang chế độ LBE tiêu chuẩn",
    1601 => "Cài đặt Tracker sang chế độ Wi-Fi LBE",
    1595 => "Cài đặt & Thiết lập Kính VR VIVE LBE",
    2337 => "Chuyên mục Hướng dẫn VIVE LBE Tracking",
    2338 => "Chuyên mục Hướng dẫn VIVE Business+",
    1549 => "Giải pháp VIVE BWS Doanh nghiệp",
    1559 => "Giải pháp VIVE Enterprise BWS",
    2123 => "Danh mục Sản phẩm Giải pháp Doanh nghiệp",
    2126 => "Danh mục Thiết bị VIVE Enterprise",
    1552 => "Hỗ trợ Thiết bị theo dõi nét mặt VIVE XR",
    1550 => "Hỗ trợ Nền tảng VIVE Business+",
    2124 => "Trung tâm Hỗ trợ Khách hàng Doanh nghiệp",
    2318 => "Hỗ trợ Giải pháp VIVE LBE Không gian lớn",
    2286 => "Trung tâm Hỗ trợ Doanh nghiệp VIVE MEA",
];

foreach ($specific_titles as $id => $title) {
    $wpdb->update($wpdb->posts, ['post_title' => $title], ['ID' => $id]);
    echo "Set ID {$id} -> '{$title}'\n";
}
