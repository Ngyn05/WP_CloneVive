<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$front_page_id = (int) get_option('page_on_front');

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY post_type, ID ASC");

echo "Processing " . count($posts) . " posts/pages/products...\n";

function clean_raw_title($title) {
    $t = trim($title);
    $t = preg_replace('/\s*[-|–—]\s*(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE|Vive\s*VN|VIVE).*$/iu', '', $t);
    $t = preg_replace('/^(?:VIVE\s*Việt\s*Nam|HTC\s*VIVE|Vive\s*VN)\s*[-|–—]\s*/iu', '', $t);
    $t = trim($t);
    if (empty($t)) {
        $t = 'Kính thực tế ảo VR';
    }
    return $t;
}

function generate_clean_keyphrase($title, $post_type, $slug) {
    $clean = clean_raw_title($title);
    
    // Xử lý các tiền tố phân loại
    $clean = preg_replace('/^(?:Doanh nghiệp|Giải pháp|Phụ kiện|Kính VR|Thiết bị|Ứng dụng|Tin tức|Trang|Chính sách)\s*:\s*/iu', '', $clean);
    
    $words = preg_split('/\s+/u', trim($clean));
    if (count($words) <= 4) {
        $kw = implode(' ', $words);
    } else {
        $kw = implode(' ', array_slice($words, 0, 4));
    }
    $kw = trim($kw, " ,.-–—:;");
    if (mb_strlen($kw) < 4) {
        $kw = 'VIVE ' . $kw;
    }
    return $kw;
}

function generate_clean_description($title, $keyphrase, $content, $excerpt, $post_type, $is_front_page) {
    if ($is_front_page) {
        return "Khám phá thế giới thực tế ảo đỉnh cao cùng HTC VIVE Việt Nam. Cung cấp kính VR cao cấp, hệ sinh thái VIVERSE và giải pháp doanh nghiệp toàn diện.";
    }
    
    $clean_title = clean_raw_title($title);
    
    // Ưu tiên trích xuất từ excerpt hoặc content nếu có câu đẹp
    $candidate = '';
    $raw_text = !empty($excerpt) ? $excerpt : $content;
    $raw_text = wp_strip_all_tags(strip_shortcodes($raw_text));
    $raw_text = html_entity_decode($raw_text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $raw_text = preg_replace('/\s+/u', ' ', $raw_text);
    $raw_text = trim($raw_text);
    
    // Tách câu
    $sentences = preg_split('/(?<=[.!?])\s+/u', $raw_text, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($sentences as $s) {
        $s = trim($s, " \t\n\r\0\x0B\"'….");
        if (mb_strlen($s) >= 40 && mb_strlen($s) <= 150 && strpos($s, '...') === false && strpos($s, '…') === false) {
            $candidate = $s . '.';
            break;
        }
    }
    
    if (empty($candidate)) {
        if ($post_type === 'product') {
            $candidate = "Tìm hiểu chi tiết {$clean_title} chính hãng tại VIVE Việt Nam. Trải nghiệm công nghệ thực tế ảo hàng đầu, bảo hành và hỗ trợ kỹ thuật tối ưu.";
        } elseif ($post_type === 'post') {
            $candidate = "Xem thông tin bài viết {$clean_title} tại VIVE Việt Nam. Cập nhật những tin tức và bước tiến công nghệ thực tế ảo mới nhất.";
        } else {
            $candidate = "Khám phá {$clean_title} tại HTC VIVE Việt Nam. Cung cấp giải pháp công nghệ thực tế ảo chuyên nghiệp, toàn diện và tối ưu trải nghiệm.";
        }
    }
    
    // Đảm bảo không quá dài và kết thúc bằng dấu chấm hoàn chỉnh, không có dấu ba chấm
    $candidate = preg_replace('/\.\.\.+/u', '.', $candidate);
    $candidate = str_replace('…', '', $candidate);
    $candidate = trim($candidate);
    
    if (mb_strlen($candidate) > 158) {
        // Cắt theo từ gần nhất trước 155 ký tự và thêm dấu chấm
        $truncated = mb_substr($candidate, 0, 154);
        $last_space = mb_strrpos($truncated, ' ');
        if ($last_space !== false && $last_space > 80) {
            $candidate = mb_substr($truncated, 0, $last_space) . '.';
        } else {
            $candidate = $truncated . '.';
        }
    }
    
    if (!preg_match('/[.!?]$/u', $candidate)) {
        $candidate .= '.';
    }
    
    return $candidate;
}

$updated_count = 0;

foreach ($posts as $p) {
    $is_front = ($p->ID === $front_page_id || $p->post_name === 'home' || $p->post_name === 'trang-chu');
    $clean_title = clean_raw_title($p->post_title);
    
    if ($is_front) {
        $seo_title = "VIVE Việt Nam | Kính thực tế ảo VR & Giải pháp XR chính hãng";
        $keyphrase = "kính thực tế ảo VIVE";
    } else {
        $seo_title = $clean_title . " | VIVE Việt Nam";
        $keyphrase = generate_clean_keyphrase($p->post_title, $p->post_type, $p->post_name);
    }
    
    $seo_desc = generate_clean_description($p->post_title, $keyphrase, $p->post_content, $p->post_excerpt, $p->post_type, $is_front);
    
    // Cập nhật Post Meta cho Yoast
    update_post_meta($p->ID, '_yoast_wpseo_title', $seo_title);
    update_post_meta($p->ID, '_yoast_wpseo_metadesc', $seo_desc);
    update_post_meta($p->ID, '_yoast_wpseo_focuskw', $keyphrase);
    
    // Cập nhật bảng wp_yoast_indexable nếu có
    $indexable = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}yoast_indexable WHERE object_type = 'post' AND object_id = %d",
        $p->ID
    ));
    
    if ($indexable) {
        $wpdb->update(
            "{$wpdb->prefix}yoast_indexable",
            array(
                'title' => $seo_title,
                'description' => $seo_desc,
                'primary_focus_keyword' => $keyphrase,
                'primary_focus_keyword_score' => 80,
                'readability_score' => 90,
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $indexable->id)
        );
    } else {
        $permalink = get_permalink($p->ID);
        $wpdb->insert(
            "{$wpdb->prefix}yoast_indexable",
            array(
                'object_id' => $p->ID,
                'object_type' => 'post',
                'object_sub_type' => $p->post_type,
                'title' => $seo_title,
                'description' => $seo_desc,
                'primary_focus_keyword' => $keyphrase,
                'primary_focus_keyword_score' => 80,
                'readability_score' => 90,
                'post_status' => 'publish',
                'is_public' => 1,
                'permalink' => $permalink,
                'permalink_hash' => !empty($permalink) ? md5($permalink) : null,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            )
        );
    }
    
    $updated_count++;
}

echo "Successfully updated SEO metadata for {$updated_count} items in database!\n";
