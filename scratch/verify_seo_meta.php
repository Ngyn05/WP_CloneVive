<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$front_page_id = (int) get_option('page_on_front');

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY post_type, ID ASC");

$missing_kw = 0;
$missing_title = 0;
$missing_desc = 0;
$ellipsis_desc = 0;
$invalid_title_suffix = 0;

$samples = [];

foreach ($posts as $p) {
    $is_front = ($p->ID === $front_page_id || $p->post_name === 'home' || $p->post_name === 'trang-chu');
    $kw = get_post_meta($p->ID, '_yoast_wpseo_focuskw', true);
    $title = get_post_meta($p->ID, '_yoast_wpseo_title', true);
    $desc = get_post_meta($p->ID, '_yoast_wpseo_metadesc', true);
    
    if (empty($kw)) $missing_kw++;
    if (empty($title)) $missing_title++;
    if (empty($desc)) $missing_desc++;
    if (strpos($desc, '...') !== false || strpos($desc, '…') !== false) $ellipsis_desc++;
    
    if ($is_front) {
        if (strpos($title, 'VIVE Việt Nam') !== 0) $invalid_title_suffix++;
    } else {
        if (!preg_match('/\|\s*VIVE\s*Việt\s*Nam$/u', $title)) $invalid_title_suffix++;
    }
    
    if (count($samples) < 10) {
        $samples[] = [
            'ID' => $p->ID,
            'Post Title' => $p->post_title,
            'Keyphrase' => $kw,
            'SEO Title' => $title,
            'Meta Description' => $desc,
            'Desc Length' => mb_strlen($desc)
        ];
    }
}

echo "Total items: " . count($posts) . PHP_EOL;
echo "Missing Keyphrase: {$missing_kw}" . PHP_EOL;
echo "Missing Title: {$missing_title}" . PHP_EOL;
echo "Missing Meta Description: {$missing_desc}" . PHP_EOL;
echo "Descriptions with ellipsis (...): {$ellipsis_desc}" . PHP_EOL;
echo "Invalid Title prefix/suffix: {$invalid_title_suffix}" . PHP_EOL;
echo PHP_EOL . "Sample check:" . PHP_EOL;
foreach ($samples as $s) {
    echo "ID {$s['ID']} | {$s['Post Title']}\n";
    echo "  KW: {$s['Keyphrase']}\n";
    echo "  SEO Title: {$s['SEO Title']}\n";
    echo "  Meta Desc ({$s['Desc Length']} chars): {$s['Meta Description']}\n\n";
}
