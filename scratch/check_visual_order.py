import urllib.request
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

url = "http://vive-vn.local/shop/products/vive-eagle/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

soup = BeautifulSoup(html, 'html.parser')

print("=== ORDER OF ELEMENTS IN RIGHT COLUMN (sidebar / product-text) ===")
widgets = soup.select('.sidebar-widget, .product-actions, .vive-woo-product__actions, .product-variants, .product-quantity')
for i, w in enumerate(widgets):
    classes = " ".join(w.get('class', []))
    title = w.select_one('.sidebar-widget__title, .product-variant__name, .product-quantity__title, a, strong')
    title_text = title.text.strip() if title else ""
    print(f"{i+1}. Tag: <{w.name} class='{classes}'> -> Title/Text: '{title_text}'")

