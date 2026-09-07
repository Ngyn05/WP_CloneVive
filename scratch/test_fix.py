with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

# If we replace the entire <div class="product-price" ...>...</div> properly
in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Test proper price replacement:
# 1. Remove installment form & terms first
html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.IGNORECASE)
html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.IGNORECASE)

# 2. Replace product-price container cleanly
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)

# Test cart form replacement
actions_html = '<div class="custom-actions">3 BUTTONS + QTY</div>'
html = re.sub(r'<product-form\b[^>]*>[\s\S]*?</product-form>', actions_html, html, count=1, flags=re.IGNORECASE)

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("Direct children count:", len(page.find_all(recursive=False)))
p_text = soup.find(class_='product-text')
print("Is p_text found?", p_text is not None)
if p_text:
    print("p_text children tags:", [f"<{c.name}> class={c.get('class')}" for c in p_text.find_all(recursive=False)])
    print("Is variants inside p_text?", p_text.find('product-variants') is not None)
    print("Is actions inside p_text?", p_text.find(class_='custom-actions') is not None)
