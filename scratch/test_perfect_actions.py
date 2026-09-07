import glob
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

files = glob.glob('shop-us/products/*/index.htm')

def clean_product_html(html, actions_html):
    # 1. Clean main-product-price
    in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'
    html = re.sub(
        r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>.*?class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>\s*</span>\s*</div>',
        '<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>',
        html,
        flags=re.DOTALL | re.IGNORECASE
    )
    
    # 2. Replace product-actions cleanly
    # Instead of deleting price-extended and replacing product-form separately,
    # let's find <div class="product-actions" ...> and replace its inner content or the whole div!
    # Let's see what is inside <div class="product-actions">
    # It ends before <div class="element--hide-on-lap"> or </div>\s*</div>\s*</product-page>
    
    # Let's match <div class="product-actions" ...> ... </div>
    # In the raw file:
    # <div class="product-actions" >...<product-form ...>...</product-form>...</div>
    # If we replace <div class="product-actions" ...> ... </product-form>\s*</div> with <div class="product-actions">actions_html</div>
    
    html = re.sub(
        r'<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>[\s\S]*?</product-form>\s*</div>',
        '<div class="product-actions">' + actions_html + '</div>',
        html,
        flags=re.IGNORECASE
    )
    
    return html

actions_html = '<div class="product-quantity">QUANTITY</div><div class="vive-woo-product__actions">ACTIONS</div><form class="vive-woo-product__callback">CALLBACK</form>'

passed = 0
failed = 0

for f in files:
    with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
        raw = fp.read()
    
    out = clean_product_html(raw, actions_html)
    soup = BeautifulSoup(out, 'html.parser')
    
    product_page = soup.find('product-page')
    if not product_page:
        print(f"Warning: no <product-page> in {f}")
        continue
    
    product_text = product_page.find('div', class_=re.compile(r'\bproduct-text\b'))
    if not product_text:
        print(f"FAIL: no .product-text in {f}")
        failed += 1
        continue
    
    actions = product_text.find('div', class_='vive-woo-product__actions')
    if not actions:
        print(f"FAIL: .vive-woo-product__actions NOT inside .product-text in {f}")
        failed += 1
        continue
    
    # Also check that direct children of product-page are the 2 main columns: gallery and product-text
    direct_divs = [c for c in product_page.children if getattr(c, 'name', None) == 'div']
    # There should be 2 direct column divs in product-page (gallery and product-text)
    if len(direct_divs) != 2:
        print(f"WARNING: direct_divs in product-page for {f} is {len(direct_divs)}")
        for d in direct_divs:
            print("  div class:", d.get('class'))
        failed += 1
        continue
        
    passed += 1

print(f"\nRESULTS: {passed} passed, {failed} failed out of {len(files)} files.")
