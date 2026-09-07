<?php
$urls = [
    'http://vive-vn.local/',
    'http://vive-vn.local/arts/',
    'http://vive-vn.local/mars/',
    'http://vive-vn.local/blog/',
    'http://vive-vn.local/newsroom/2025/',
    'http://vive-vn.local/shop/collections/',
    'http://vive-vn.local/shop/products/21071655/',
    'http://vive-vn.local/shop/products/vive-eagle/',
    'http://vive-vn.local/shop/products/vive-flow-multiport-power-dongle/',
];

foreach ($urls as $u) {
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $content = @file_get_contents($u, false, $ctx);
    if ($content && preg_match('#<title\b[^>]*>(.*?)</title>#is', $content, $m)) {
        $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        echo "URL: {$u}\n  <title>: {$title}\n\n";
    } else {
        echo "URL: {$u}\n  <title>: NOT FOUND or ERROR\n\n";
    }
}
