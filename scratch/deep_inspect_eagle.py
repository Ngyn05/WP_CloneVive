import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

print("1. All Elements with id containing 'main':")
for tag in soup.find_all(id=re.compile(r'main', re.I)):
    print(f"<{tag.name} id='{tag.get('id')}' class='{tag.get('class')}'>")

print("\n2. All product-variants tags:")
for pv in soup.find_all(['product-variants', 'div'], class_=re.compile(r'product-variant', re.I)):
    if pv.name == 'product-variants' or 'product-variants' in pv.get('class', []):
        print(f"<{pv.name} class='{pv.get('class')}'> children={len(pv.find_all(class_='product-variant'))}")

print("\n3. Inspect Color Variant CSS / visibility:")
color_opts = soup.select('[data-name*="color"]')
print(f"Found {len(color_opts)} color option blocks:")
for c in color_opts:
    print(c.prettify())

print("\n4. Inspect Quantity HTML:")
for q in soup.select('product-quantity, .product-quantity'):
    print(q.prettify())
