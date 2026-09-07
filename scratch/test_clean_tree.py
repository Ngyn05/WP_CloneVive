with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# 1. Clean out the top price block (main-product-price) completely without leaving any orphaned closing tags:
html = re.sub(
    r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>\s*<input[^>]*>\s*<input[^>]*>\s*</form>\s*</span>\s*</div>',
    '<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>',
    html,
    flags=re.IGNORECASE
)

# 2. In shop product pages, replace the bottom price + cart form inside product-actions
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
    '<form class="vive-woo-product__callback">CALLBACK FORM</form>'
)

html = re.sub(
    r'<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>[\s\S]*?</product-form>\s*</div>',
    '<div class="product-actions">' + actions_html + '</div>',
    html,
    count=1,
    flags=re.IGNORECASE
)

# 3. Clean up any remaining payment buttons / tryon
html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.IGNORECASE)
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bshopify-payment-button\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '', html, flags=re.IGNORECASE)

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("Product-page direct children count:", len(page.find_all(recursive=False)))
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
