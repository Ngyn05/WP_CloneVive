with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Match from <div...main-product-price...> up to </form>\s*</span>\s*</div>
p_price_block = re.compile(r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>.*?class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>\s*</span>\s*</div>', re.DOTALL | re.IGNORECASE)
m = p_price_block.search(html)
print("Found main-product-price block?", m is not None)
if m:
    print("Matched length:", len(m.group(0)))
    print("Matched ends with:", repr(m.group(0)[-60:]))

html = p_price_block.sub(r'<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>', html)

# Replace product-actions
actions_html = '<div class="product-quantity">QTY</div><div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div><form class="vive-woo-product__callback">CALLBACK</form>'
html = re.sub(r'<product-form\b[^>]*>.*?</product-form>', actions_html, html, count=1, flags=re.DOTALL | re.IGNORECASE)

# Replace price in sidebar-widget inside product-actions
html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>', '<span class="sidebar-widget__title">Tình trạng</span>', html, flags=re.I)
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>.*?</div>', '<div class="product-price">' + in_stock_html + '</div>', html, flags=re.DOTALL | re.I)

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("\nDirect children count of product-page:", len(page.find_all(recursive=False)))
for c in page.find_all(recursive=False):
    print(f"  <{c.name}> class={c.get('class')}")

p_text = soup.find(class_='product-text')
print("\nIs p_text found?", p_text is not None)
if p_text:
    print("p_text direct children count:", len(p_text.find_all(recursive=False)))
    for c in p_text.find_all(recursive=False):
        print(f"    <{c.name}> class={c.get('class')}")
    print("\nIs product-variants inside p_text?", p_text.find('product-variants') is not None)
    print("Is custom-actions inside p_text?", p_text.find(class_='vive-woo-product__actions') is not None)
