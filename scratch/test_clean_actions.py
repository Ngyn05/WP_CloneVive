with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Match from <div...main-product-price...> up to </form>\s*</span>\s*</div>
p_price_block = re.compile(r'<div\b[^>]*class=["\'][^"\']*\bmain-product-price\b[^"\']*["\'][^>]*>.*?class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>.*?</form>\s*</span>\s*</div>', re.DOTALL | re.IGNORECASE)
html = p_price_block.sub(r'<div class="main-product-price" style="margin: 16px 0 20px;"><div class="product-price">' + in_stock_html + '</div></div>', html)

# In product-actions: replace the entire sidebar-widget for price AND product-form with actions_html!
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

# Replace the entire contents inside product-actions
html = re.sub(
    r'(<div\b[^>]*class=["\'][^"\']*\bproduct-actions\b[^"\']*["\'][^>]*>).*?(</div>\s*</div>\s*</product-page>)',
    r'\1' + actions_html + r'\2',
    html,
    flags=re.DOTALL | re.IGNORECASE
)

# Replace item and sticky prices
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>.*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.DOTALL | re.I)
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>.*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.DOTALL | re.I)

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
