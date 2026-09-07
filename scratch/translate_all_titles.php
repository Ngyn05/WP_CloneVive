<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$translate_map = [
    // Trang chuyên mục / mục chính
    'Nhà phát triển: Hardware Guides' => 'Nhà phát triển: Hướng dẫn phần cứng thiết bị',
    'Nhà phát triển: Software Guides' => 'Nhà phát triển: Hướng dẫn phần mềm và SDK',
    'Nhà phát triển: Guides' => 'Nhà phát triển: Cẩm nang và tài liệu kỹ thuật',
    'Nhà phát triển: Documentation' => 'Nhà phát triển: Tài liệu tích hợp hệ thống',
    'Doanh nghiệp: Support' => 'Trung tâm Hỗ trợ Khách hàng Doanh nghiệp',
    'Doanh nghiệp: Product' => 'Danh mục Sản phẩm Giải pháp Doanh nghiệp',
    'Doanh nghiệp: Solutions' => 'Giải pháp Thực tế ảo Doanh nghiệp',
    'Doanh nghiệp: Resources' => 'Tài nguyên & Tài liệu Doanh nghiệp',
    'Doanh nghiệp: Case Studies' => 'Dự án Tiêu biểu & Câu chuyện Khách hàng',
    'Doanh nghiệp: Overview' => 'Tổng quan Giải pháp Doanh nghiệp VIVE',
    
    // Trang gốc
    'Product' => 'Danh mục Kính thực tế ảo & Sản phẩm VIVE',
    'Setup' => 'Hướng dẫn Cài đặt & Thiết lập Thiết bị',
    'Support' => 'Trung tâm Hỗ trợ Kỹ thuật & Bảo hành VIVE',
    'Accessory' => 'Danh mục Phụ kiện Kính thực tế ảo VIVE',
    'Accessories' => 'Danh mục Phụ kiện Kính thực tế ảo VIVE',
    'Legal' => 'Điều khoản Pháp lý & Quy định Sử dụng',
    'Terms' => 'Điều khoản Dịch vụ & Chính sách Bảo hành',
    'Wireless' => 'Giải pháp Kết nối Không dây VIVE Wireless',
    'Solutions' => 'Giải pháp Thực tế ảo Toàn diện VIVE',
    
    // Phụ kiện & Hướng dẫn kỹ thuật
    'Multi Battery Charger For Vive Focus Series' => 'Bộ sạc đa pin cho dòng VIVE Focus',
    'Narrow Face Cushion 2 Vive Pro' => 'Bộ đệm mặt bản hẹp cho VIVE Pro (2 chiếc)',
    'Vive Ac Adapter 30W' => 'Củ sạc nhanh VIVE AC Adapter 30W',
    'Vive Deluxe Audio Strap' => 'Dây đeo tích hợp tai nghe cao cấp VIVE Deluxe Audio Strap',
    'Vive Dp Connect Cable Pro' => 'Cáp kết nối DisplayPort cho VIVE Pro',
    'Vive Pro Wireless Adapter Attachment Kit' => 'Bộ phụ kiện gắn bộ chuyển đổi không dây VIVE Pro',
    'Vive Wired Streaming Cable' => 'Cáp truyền phát trực tiếp có dây VIVE',
    'Vive Wired Streaming Converter' => 'Bộ chuyển đổi truyền phát trực tiếp VIVE',
    'Vive Wired Streaming Kit' => 'Bộ kết nối truyền phát có dây VIVE',
    'Vive Wireless Dongle' => 'Bộ thu phát không dây VIVE Wireless Dongle',
    'Wireless Adapter Full Pack' => 'Bộ phụ kiện kết nối không dây VIVE Wireless Adapter',
    'Xr Battery Cradle' => 'Đế gắn pin tăng cường cho VIVE XR Elite',
    'Cosmos External Tracking Faceplate' => 'Mặt nạ cảm biến định vị ngoài VIVE Cosmos',
    'Vive Ultimate Tracker' => 'Cảm biến theo dõi toàn thân VIVE Ultimate Tracker',
    'Ultimate Tracker' => 'Cảm biến theo dõi chuyển động VIVE Ultimate Tracker',
    'Wireless Adapter' => 'Bộ chuyển đổi không dây VIVE Wireless Adapter',
    'Wireless Tracker' => 'Cảm biến định vị không dây VIVE Wireless Tracker',
    'Wrist Tracker' => 'Cảm biến theo dõi cổ tay VIVE Wrist Tracker',
    'Vive Xr Face Tracker' => 'Thiết bị theo dõi nét mặt VIVE Facial Tracker',
    'Face Tracker' => 'Thiết bị theo dõi nét mặt VIVE Facial Tracker',
    'Eye Tracker' => 'Thiết bị theo dõi chuyển động mắt VIVE Eye Tracker',
    'TrackStraps For Vive Thiết bị theo dõi toàn thân VIVE Ultimate Tracker Plus Dance Dash' => 'Dây đeo gắn cảm biến VIVE Ultimate Tracker kèm tựa game Dance Dash',
];

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product')");

$updated = 0;
foreach ($posts as $p) {
    $title = $p->post_title;
    $new_title = $title;
    
    // Áp dụng bảng dịch chính xác
    foreach ($translate_map as $en => $vi) {
        if (trim($new_title) === $en) {
            $new_title = $vi;
            break;
        }
        if (stripos($new_title, $en) !== false) {
            $new_title = str_ireplace($en, $vi, $new_title);
        }
    }
    
    // Dọn dẹp các cụm từ tiếng Anh nhỏ còn sót lại
    $word_replace = [
        'Hardware Guides' => 'Hướng dẫn phần cứng',
        'Software Guides' => 'Hướng dẫn phần mềm',
        'Hardware Guide' => 'Hướng dẫn phần cứng',
        'Software Guide' => 'Hướng dẫn phần mềm',
        'Developer' => 'Nhà phát triển',
        'Developers' => 'Nhà phát triển',
        'Overview' => 'Tổng quan',
        'Specs' => 'Thông số kỹ thuật',
        'Features' => 'Tính năng nổi bật',
        'Support' => 'Hỗ trợ kỹ thuật',
        'Product' => 'Sản phẩm',
        'Products' => 'Sản phẩm',
        'Solutions' => 'Giải pháp',
        'Resources' => 'Tài nguyên',
        'Legal' => 'Pháp lý',
        'Accessories' => 'Phụ kiện',
        'Accessory' => 'Phụ kiện',
        'Download' => 'Tải xuống',
        'Downloads' => 'Tải xuống',
        'Cookie Preferences' => 'Tùy chọn Cookie',
        'Cookies' => 'Chính sách Cookie',
        'Cookie' => 'Cookie',
        'Howto' => 'Hướng dẫn',
        'Guide' => 'Hướng dẫn',
        'Guides' => 'Hướng dẫn',
        'Setup' => 'Cài đặt thiết bị',
        'Setting' => 'Cài đặt',
        'Settings' => 'Cài đặt',
    ];
    
    foreach ($word_replace as $w_en => $w_vi) {
        $new_title = preg_replace('/\b' . preg_quote($w_en, '/') . '\b/iu', $w_vi, $new_title);
    }
    
    // Làm sạch ký tự thừa, khoảng trắng thừa
    $new_title = preg_replace('/\s+/u', ' ', trim($new_title));
    $new_title = preg_replace('/\s*:\s*$/u', '', $new_title);
    
    if ($new_title !== $p->post_title) {
        $wpdb->update($wpdb->posts, ['post_title' => $new_title], ['ID' => $p->ID]);
        echo "Updated ID {$p->ID}: '{$p->post_title}' -> '{$new_title}'\n";
        $updated++;
    }
}

echo "\nTranslated and updated {$updated} titles to Vietnamese!\n";
