import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# 1. Clean installment form first
html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.IGNORECASE)
html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.IGNORECASE)

# 2. Replace price spans cleanly without touching container div
html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bproduct-price--original\b[^"\']*["\'][^>]*>[\s\S]*?</span>', in_stock_html, html, flags=re.IGNORECASE)
html = re.sub(r'<del\b[^>]*class=["\'][^"\']*\bproduct-price--compare\b[^"\']*["\'][^>]*>[\s\S]*?</del>', '', html, flags=re.IGNORECASE)
html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>', '<span class="sidebar-widget__title">Tình trạng</span>', html, flags=re.IGNORECASE)

# 3. Unhide product-variants
html = html.replace('product-variants no-js-hidden', 'product-variants')
html = html.replace('data-hide-variants="true"', 'data-hide-variants="false"')

# 4. Replace main add-to-cart form
quantity_html = '<div class="product-quantity quantity-selector-holder"><span class="product-quantity__title" style="font-weight:700;font-size:14px;color:#111;margin-right:12px;">Số lượng:</span><button type="button" class="qty-button qty-minus" aria-label="Giảm số lượng">−</button><input type="number" name="quantity" value="1" min="1" max="99" class="qty qty-selector product__quantity" aria-label="Số lượng"><button type="button" class="qty-button qty-plus" aria-label="Tăng số lượng">+</button></div>'
actions_html = quantity_html + '<div class="vive-woo-product__actions"><a class="vive-woo-product__action vive-woo-product__action--zalo" href="https://zalo.me/0917834532" target="_blank">TƯ VẤN QUA ZALO</a><a class="vive-woo-product__action vive-woo-product__action--buy" href="#dat-hang" data-theme-vive-buy-slug="vive-eagle" data-theme-vive-buy-mode="popup">MUA NGAY</a></div><form class="vive-woo-product__callback"><strong>Hãy để lại số điện thoại, chúng tôi sẽ gọi tư vấn miễn phí!</strong><div><input name="phone" type="tel" placeholder="Nhập số điện thoại..." required><button type="submit">GỬI ĐI</button></div></form>'

html = re.sub(r'<product-form\b[^>]*id=["\']add-to-cart-[^"\']*["\'][^>]*>[\s\S]*?</product-form>', actions_html, html, flags=re.IGNORECASE)

soup = BeautifulSoup(html, 'html.parser')

product_text = soup.find(class_='product-text')
print("=== What is INSIDE .product-text ===")
if product_text:
    for c in product_text.children:
        if c.name:
            print(f"- <{c.name} class='{c.get('class')}'> text='{c.text.strip()[:40]}...'")
else:
    print(".product-text not found")

print("\n=== What is OUTSIDE .product-text (siblings) ===")
if product_text:
    for sib in list(product_text.next_siblings)[:10]:
        if getattr(sib, 'name', None):
            print(f"- <{sib.name} class='{sib.get('class')}'>")
