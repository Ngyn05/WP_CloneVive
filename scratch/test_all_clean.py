import glob
import os
import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'
actions_html = (
    '<div class="product-quantity quantity-selector-holder">'
    '<span class="product-quantity__title" style="font-weight:700;font-size:14px;color:#111;margin-right:12px;">Số lượng:</span>'
    '<button type="button" class="qty-button qty-minus">−</button>'
    '<input type="number" name="quantity" value="1" min="1" max="99" class="qty qty-selector product__quantity">'
    '<button type="button" class="qty-button qty-plus">+</button>'
    '</div>'
    '<div class="vive-woo-product__actions">'
    '<a class="vive-woo-product__action vive-woo-product__action--zalo" href="https://zalo.me/0917834532" target="_blank">TƯ VẤN QUA ZALO</a>'
    '<a class="vive-woo-product__action vive-woo-product__action--buy" href="#dat-hang">MUA NGAY</a>'
    '</div>'
    '<form class="vive-woo-product__callback">CALLBACK</form>'
)

product_files = glob.glob('shop-us/products/*/index.htm')
print(f"Testing across all {len(product_files)} products...")

broken_count = 0
for pf in product_files:
    with open(pf, 'r', encoding='utf-8') as f:
        html = f.read()

    # 1. Clean out the top price block (main-product-price)
    if re.search(r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b', html, re.I):
        html = re.sub(
            r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>.*?class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>\s*</span>\s*</div>',
            '<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>',
            html,
            count=1,
            flags=re.DOTALL | re.IGNORECASE
        )
    
    # 2. In product-actions, replace the price widget + form with actions_html
    if re.search(r'<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b', html, re.I):
        html = re.sub(
            r'(<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>).*?(</div>\s*</div>\s*</product-page>)',
            r'\1' + actions_html + r'\2',
            html,
            count=1,
            flags=re.DOTALL | re.IGNORECASE
        )
    elif re.search(r'<product-form\b', html, re.I):
        html = re.sub(r'<product-form\b[^>]*>.*?</product-form>', actions_html, html, count=1, flags=re.DOTALL | re.IGNORECASE)

    # 3. Clean item and sticky prices
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>.*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.DOTALL | re.I)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>.*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.DOTALL | re.I)

    soup = BeautifulSoup(html, 'html.parser')
    page = soup.find('product-page')
    if page:
        children = page.find_all(recursive=False)
        if len(children) != 2:
            print(f"[FAIL] {os.path.basename(os.path.dirname(pf))} has {len(children)} direct children!")
            broken_count += 1
        p_text = soup.find(class_='product-text')
        if not p_text or not p_text.find(class_='vive-woo-product__actions'):
            print(f"[FAIL-ACTIONS] {os.path.basename(os.path.dirname(pf))} does not have actions in p_text!")
            broken_count += 1

print(f"RESULTS: {len(product_files) - broken_count} / {len(product_files)} passed! (Failures: {broken_count})")
