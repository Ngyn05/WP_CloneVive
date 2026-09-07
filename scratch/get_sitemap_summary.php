<?php
require_once 'c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-load.php';

$sitemaps = [
    'Page Sitemap' => home_url('/page-sitemap.xml'),
    'Post Sitemap (Blog)' => home_url('/post-sitemap.xml'),
    'Product Sitemap (Shop)' => home_url('/product-sitemap.xml'),
    'Product Category Sitemap' => home_url('/product_cat-sitemap.xml'),
];

$summary = [];
foreach ($sitemaps as $name => $url) {
    $xml_content = @file_get_contents($url);
    if ($xml_content) {
        $count = substr_count($xml_content, '<loc>');
        $summary[] = [
            'name' => $name,
            'url' => $url,
            'url_count' => $count,
            'status' => '200 OK'
        ];
    } else {
        $summary[] = [
            'name' => $name,
            'url' => $url,
            'url_count' => 0,
            'status' => 'FAILED'
        ];
    }
}

file_put_contents(
    get_template_directory() . '/scratch/sitemap_summary.json',
    json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

print_r($summary);
