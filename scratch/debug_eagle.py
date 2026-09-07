with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

def c(name, h):
    soup = BeautifulSoup(h, 'html.parser')
    page = soup.find('product-page')
    print(f"[{name}] direct children: {len(page.find_all(recursive=False))}")

c("Raw", html)

html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.I)
c("1. Form removal", html)

html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.I)
c("2. Payment terms removal", html)

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Replace product-price
html = re.sub(r'(<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>)[\s\S]*?(</div>)', r'\1' + in_stock_html + r'\2', html, flags=re.I)
c("3. product-price replace", html)
