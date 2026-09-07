import glob
import os
import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'
actions_html = '<div class="product-quantity">QTY</div><div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div><form class="vive-woo-product__callback">CALLBACK</form>'

product_files = glob.glob('shop-us/products/*/index.htm')
print(f"Testing new logic on {len(product_files)} products...")

broken_count = 0
for pf in product_files:
    with open(pf, 'r', encoding='utf-8') as f:
        html = f.read()

    # Step 1: Remove installment form & shopify payment terms FIRST
    html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.I)
    html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.I)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bshopify-payment-button\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '', html, flags=re.I)

    # Step 2: Replace span show-block-if-variant-selected OR div product-price
    html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bshow-block-if-variant-selected\b[^"\']*["\'][^>]*>[\s\S]*?</span>', in_stock_html, html, flags=re.I)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-price">' + in_stock_html + '</div>', html, flags=re.I)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.I)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.I)
    html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>', '<span class="sidebar-widget__title">Tình trạng</span>', html, flags=re.I)

    # Step 3: Replace product-form with actions_html
    if re.search(r'<product-form\b', html, re.I):
        html = re.sub(r'<product-form\b[^>]*>[\s\S]*?</product-form>', actions_html, html, count=1, flags=re.I)
    elif re.search(r'<form\b[^>]*action=["\'][^"\']*/cart/add', html, re.I):
        html = re.sub(r'<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>[\s\S]*?</form>', actions_html, html, count=1, flags=re.I)

    soup = BeautifulSoup(html, 'html.parser')
    page = soup.find('product-page')
    if page:
        children = page.find_all(recursive=False)
        if len(children) != 2:
            print(f"[FAIL] {os.path.basename(os.path.dirname(pf))} has {len(children)} direct children!")
            broken_count += 1

print(f"DONE! Failures: {broken_count} out of {len(product_files)}")
