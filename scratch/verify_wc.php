<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$all = wc_get_products( array( 'limit' => -1 ) );
echo "Total WC Products: " . count($all) . "\n";

$instock = 0;
$with_price = 0;
$sample_data = array();

foreach ( $all as $p ) {
    if ( $p->is_in_stock() ) {
        $instock++;
    }
    if ( (float) $p->get_price() > 0 ) {
        $with_price++;
    }
    if ( count($sample_data) < 15 ) {
        $sample_data[] = array(
            'id'    => $p->get_id(),
            'name'  => $p->get_name(),
            'price' => wc_price( $p->get_price() ),
            'raw'   => $p->get_price(),
            'stock' => $p->get_stock_status(),
            'sku'   => $p->get_sku(),
        );
    }
}

echo "In Stock Count: {$instock} / " . count($all) . "\n";
echo "With Price Count: {$with_price} / " . count($all) . "\n\n";
echo "SAMPLE PRODUCTS:\n";
foreach ( $sample_data as $s ) {
    $clean_price = html_entity_decode( strip_tags( $s['price'] ), ENT_QUOTES, 'UTF-8' );
    echo "• [ID: {$s['id']}] {$s['name']}\n";
    echo "   -> Giá: {$clean_price} (Số raw: {$s['raw']} VND) | Trạng thái: {$s['stock']} | SKU: {$s['sku']}\n";
}
