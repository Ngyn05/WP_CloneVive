<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product')");

foreach ($posts as $p) {
    $title = $p->post_title;
    
    // Khử các cụm từ trùng lặp
    $cleanups = [
        'Bộ kết nối truyền phát có dây Bộ kết nối truyền phát có dây VIVE' => 'Bộ kết nối truyền phát có dây VIVE Wired Streaming Kit',
        'Bộ kết nối truyền phát có dây Bộ kết nối truyền phát có dây VIVE (SB)' => 'Bộ kết nối truyền phát có dây VIVE Wired Streaming Kit (SB)',
        'Dây đeo tích hợp tai nghe cao cấp Dây đeo tích hợp tai nghe cao cấp VIVE Deluxe Audio Strap' => 'Dây đeo tích hợp tai nghe cao cấp VIVE Deluxe Audio Strap',
        'Mặt nạ hỗ trợ định vị ngoài VIVE Mặt nạ cảm biến định vị ngoài VIVE Cosmos' => 'Mặt nạ cảm biến định vị ngoài VIVE Cosmos External Tracking',
        'Thiết bị theo dõi chuyển động mắt VIVE Focus 3 Thiết bị theo dõi chuyển động mắt VIVE Eye Tracker' => 'Thiết bị theo dõi chuyển động mắt VIVE Focus 3 Eye Tracker',
        'Bộ thu phát không dây VIVE Giải pháp Kết nối Không dây Bộ thu phát không dây VIVE Wireless Dongle' => 'Bộ thu phát không dây VIVE Wireless Dongle',
        'Cảm biến theo dõi chuyển động độc lập Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker' => 'Cảm biến theo dõi chuyển động toàn thân VIVE Ultimate Tracker',
        'Bộ dây đeo Trackstraps cho Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker (Đeo chân &amp; eo)' => 'Bộ dây đeo TrackStraps cho VIVE Ultimate Tracker (Đeo chân & eo)',
        'Cáp truyền phát dữ liệu có dây Cáp truyền phát trực tiếp có dây VIVE (5m)' => 'Cáp truyền phát dữ liệu có dây VIVE Wired Streaming Cable (5m)',
        'Bộ chuyển đổi truyền phát dữ liệu có dây Bộ chuyển đổi truyền phát trực tiếp VIVE' => 'Bộ chuyển đổi truyền phát trực tiếp VIVE Wired Streaming Converter',
        'Bộ theo dõi chuyển động Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker 3+1 Kit (3 Tracker + 1 Dongle)' => 'Bộ cảm biến theo dõi toàn thân VIVE Ultimate Tracker 3+1 Kit',
        'Bộ theo dõi chuyển động Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker 3+1 Kit' => 'Bộ cảm biến theo dõi toàn thân VIVE Ultimate Tracker 3+1 Kit',
        'Phụ kiện: Vive Pro Giải pháp Kết nối Không dây VIVE Bộ chuyển đổi không dây VIVE Wireless Adapter Attachment Kit' => 'Phụ kiện: Bộ gắn kết nối không dây VIVE Pro Wireless Adapter Kit',
        'Phụ kiện: Vive Thiết bị theo dõi toàn thân Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker' => 'Phụ kiện: Cảm biến theo dõi toàn thân VIVE Ultimate Tracker',
        'Phụ kiện: Vive Giải pháp Kết nối Không dây Bộ thu phát không dây VIVE Wireless Dongle' => 'Phụ kiện: Bộ thu phát không dây VIVE Wireless Dongle',
        'Phụ kiện: Vòng đeo cổ tay định vị VIVE Cảm biến theo dõi cổ tay VIVE Wrist Tracker' => 'Phụ kiện: Cảm biến theo dõi cổ tay VIVE Wrist Tracker',
        'Phụ kiện: Giải pháp Kết nối Không dây VIVE Bộ phụ kiện kết nối không dây VIVE Bộ chuyển đổi không dây VIVE Wireless Adapter' => 'Phụ kiện: Bộ kết nối không dây VIVE Wireless Adapter Full Pack',
        'Phụ kiện: Dây đeo định vị TrackStraps For Vive Thiết bị theo dõi toàn thân Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker Plus Dance Dash' => 'Phụ kiện: Bộ dây đeo TrackStraps cho VIVE Ultimate Tracker kèm game Dance Dash',
        'Gói bản quyền VIVE Business+ Cảm biến theo dõi chuyển động VIVE Ultimate Tracker 3 năm (5 tài khoản)' => 'Gói bản quyền VIVE Business+ Ultimate Tracker 3 năm (5 tài khoản)',
        'Nhà phát triển: Cảm biến theo dõi toàn thân VIVE Cảm biến theo dõi chuyển động VIVE Ultimate Tracker Guidelines' => 'Nhà phát triển: Hướng dẫn tích hợp cảm biến VIVE Ultimate Tracker',
        'Doanh nghiệp: Vive Focus 3 Thiết bị theo dõi chuyển động mắt VIVE Eye Tracker' => 'Doanh nghiệp: Thiết bị theo dõi mắt cho VIVE Focus 3',
        'Hướng dẫn hỗ trợ kỹ thuật: Giải pháp Kết nối Không dây VIVE Bộ chuyển đổi không dây VIVE Wireless Adapter' => 'Hướng dẫn kỹ thuật: Bộ kết nối không dây VIVE Wireless Adapter',
        'Hướng dẫn hỗ trợ kỹ thuật: Giải pháp Kết nối Không dây VIVE Cảm biến định vị không dây VIVE Wireless Tracker' => 'Hướng dẫn kỹ thuật: Cảm biến định vị không dây VIVE Wireless Tracker',
    ];
    
    $new_title = $title;
    foreach ($cleanups as $bad => $good) {
        if (trim($new_title) === $bad || stripos($new_title, $bad) !== false) {
            $new_title = str_ireplace($bad, $good, $new_title);
        }
    }
    
    // Dọn dẹp các tiền tố "Phụ kiện: Vive" hoặc "Hướng dẫn hỗ trợ kỹ thuật:"
    $new_title = preg_replace('/\bVive\s+Thiết bị theo dõi toàn thân\b/iu', 'Thiết bị theo dõi toàn thân', $new_title);
    $new_title = preg_replace('/\s+/u', ' ', trim($new_title));
    
    if ($new_title !== $p->post_title) {
        $wpdb->update($wpdb->posts, ['post_title' => $new_title], ['ID' => $p->ID]);
        echo "Cleaned ID {$p->ID}: '{$new_title}'\n";
    }
}
