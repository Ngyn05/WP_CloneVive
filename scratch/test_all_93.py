import glob
import os
import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'
actions_html = '<div class="product-actions"><div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div></div>'

product_files = glob.glob('shop-us/products/*/index.htm')
print(f"Testing all {len(product_files)} products...")

broken_count = 0
for pf in product_files:
    with open(pf, 'r', encoding='utf-8') as f:
        html = f.read()

    # 1. Clean out the top price block (main-product-price) cleanly
    html = re.sub(
        r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>[\s\S]*?</span>\s*</div>',
        '<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>',
        html,
        count=1,
        flags=re.IGNORECASE
    )
    
    # 2. Replace product-actions
    if re.search(r'<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b', html, re.I):
        html = re.sub(
            r'<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>[\s\S]*?(?:</product-form>|</form>)\s*</div>',
            actions_html,
            html,
            count=1,
            flags=re.IGNORECASE
        )
    elif re.search(r'<product-form\b', html, re.I):
        html = re.sub(r'<product-form\b[^>]*>[\s\S]*?</product-form>', actions_html, html, count=1, flags=re.IGNORECASE)

    # Clean other prices
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
    html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)

    soup = BeautifulSoup(html, 'html.parser')
    page = soup.find('product-page')
    if page:
        children = page.find_all(recursive=False)
        if len(children) != 2:
            print(f"[FAIL] {os.path.basename(os.path.dirname(pf))} has {len(children)} direct children!")
            broken_count += 1

print(f"Done! Total failures out of {len(product_files)}: {broken_count}")
