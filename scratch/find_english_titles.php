<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

$posts = $wpdb->get_results("SELECT ID, post_title, post_name, post_type FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('page', 'post', 'product') ORDER BY ID ASC");

$english_words = [
    'Hardware Guides', 'Software Guides', 'Support', 'Product', 'Products', 'Overview', 'Legal', 
    'Terms', 'Accessory', 'Accessories', 'Case Studies', 'Developer', 'Developers', 'Solutions', 
    'Resources', 'Download', 'Downloads', 'Pricing', 'Specs', 'Specifications', 'Features', 
    'Contact', 'About', 'Careers', 'Press', 'Docs', 'Documentation', 'Howto', 'Setup', 
    'Setting', 'Device', 'Devices', 'Guide', 'Guides', 'Console', 'Store', 'Cart', 'Checkout', 
    'Account', 'My Account', 'Privacy', 'Security', 'Policy', 'Policies', 'Cookies', 'Cookie',
    'Tracking', 'Wireless', 'Wired', 'Battery', 'Controller', 'Controllers', 'Tracker', 'Trackers',
    'Face Tracker', 'Eye Tracker', 'Audio', 'Cable', 'Adapter', 'Mount', 'Cushion', 'Strap',
    'Software', 'Enterprise', 'Business', 'Edition', 'Bundle', 'Kit', 'Pack', 'Subscription',
    'Seat', 'Seats', 'Year', 'Month', 'Day'
];

$found = [];
foreach ($posts as $p) {
    $has_en = false;
    foreach ($english_words as $w) {
        if (preg_match('/\b' . preg_quote($w, '/') . '\b/i', $p->post_title)) {
            $has_en = true;
            break;
        }
    }
    if ($has_en) {
        $found[] = $p;
    }
}

echo "Found " . count($found) . " posts with English terms in title:\n";
foreach (array_slice($found, 0, 30) as $f) {
    echo "ID {$f->ID} | {$f->post_type} | {$f->post_title} (slug: {$f->post_name})\n";
}
