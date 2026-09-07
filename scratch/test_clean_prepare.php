<?php
$html = file_get_contents('shop-us/products/vive-eagle/index.htm');

$in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>';

// 1. Remove installment form & shopify payment terms first
$html = preg_replace( '#<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>#is', '', $html );
$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bshopify-payment-button\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '', $html );
$html = preg_replace( '#<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>#is', '', $html );

// 2. Replace all product price blocks with in-stock badge (without touching span product-price--original directly)
$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '<div class="product-price">' . $in_stock_html . '</div>', $html );
$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '<div class="product-item__price">' . $in_stock_html . '</div>', $html );
$html = preg_replace( '#<span\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price-varies\b[^"\']*["\'][^>]*>[\s\S]*?</span>#is', $in_stock_html, $html );
$html = preg_replace( '#<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>#is', '<div class="sticky-add-to-cart__price">' . $in_stock_html . '</div>', $html );
$html = preg_replace( '#<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>#is', '<span class="sidebar-widget__title">Tình trạng</span>', $html );
$html = preg_replace( '#>\s*(?:Đang được bán từ\s*)?\$[0-9]+[0-9\.,]*\s*(?:USD)?\s*<#iu', '>' . $in_stock_html . '<', $html );

// 3. Actions HTML
$actions_html = '<div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div>';
if ( preg_match( '#<product-form\b[^>]*>[\s\S]*?</product-form>#is', $html ) ) {
    $html = preg_replace( '#<product-form\b[^>]*>[\s\S]*?</product-form>#is', $actions_html, $html, 1 );
} elseif ( preg_match( '#<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>[\s\S]*?</form>#is', $html ) ) {
    $html = preg_replace( '#<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>[\s\S]*?</form>#is', $actions_html, $html, 1 );
}

file_put_contents('scratch/test_out.html', $html);
echo "SUCCESS writing test_out.html\n";
