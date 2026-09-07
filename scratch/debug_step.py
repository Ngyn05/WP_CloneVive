with open('shop-us/products/2104005/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

def c(name, h):
    soup = BeautifulSoup(h, 'html.parser')
    page = soup.find('product-page')
    print(f"[{name}] direct children: {len(page.find_all(recursive=False))}")

c("Raw", html)

html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.I)
c("After form removal", html)

html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.I)
c("After payment terms removal", html)

html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bshopify-payment-button\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '', html, flags=re.I)
c("After payment button removal", html)

html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-price">STOCK</div>', html, flags=re.I)
c("After product-price replace", html)
