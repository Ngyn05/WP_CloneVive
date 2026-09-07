import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

product_page = soup.find('product-page')
if product_page:
    print("Found <product-page> with children:")
    for child in product_page.children:
        if child.name:
            print(f"Child: <{child.name} class='{child.get('class')}'> id='{child.get('id')}'>")
            # Check what's inside child
            variants = child.find('product-variants')
            forms = child.find_all(['product-form', 'form'])
            print(f"   -> has variants: {bool(variants)}, forms: {len(forms)}")
else:
    print("No <product-page> found!")
