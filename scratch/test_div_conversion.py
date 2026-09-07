with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Test 1: Change <span class="show-block-if-variant-selected"> to <div class="show-block-if-variant-selected">
# and </span> to </div> before closing main-product-price
html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.I)
html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.I)

# Replace <span class="show-block-if-variant-selected"> with <div class="show-block-if-variant-selected">
html = re.sub(r'<span(\b[^>]*class=["\'][^"\']*\bshow-block-if-variant-selected\b[^"\']*["\'][^>]*)>', r'<div\1>', html, flags=re.I)
html = re.sub(r'</span>(\s*</div>\s*<div\b[^>]*class=["\'][^"\']*(?:product-custom-liquid|sidebar-widget|product-actions)\b)', r'</div>\1', html, flags=re.I)

# Replace product-price
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-price">' + in_stock_html + '</div>', html, flags=re.I)

# Replace product-form
actions_html = '<div class="product-quantity">QTY</div><div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div><form class="vive-woo-product__callback">CALLBACK</form>'
html = re.sub(r'<product-form\b[^>]*>[\s\S]*?</product-form>', actions_html, html, count=1, flags=re.I)

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("Direct children count:", len(page.find_all(recursive=False)))
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
