import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

product_text = soup.find(class_='product-text')
if product_text:
    print("Found .product-text with children:")
    for child in product_text.children:
        if child.name:
            print(f"- <{child.name} class='{child.get('class')}'> text='{child.text.strip()[:60]}...'")
