import urllib.request
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

# Fetch local vive-eagle HTML
url = "http://vive-vn.local/shop/products/vive-eagle/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

soup = BeautifulSoup(html, 'html.parser')

print("=== CHECKING FOR DUPLICATE MAIN PRODUCT SECTIONS ===")
main_sections = soup.find_all(lambda tag: tag.get('id', '').startswith('shopify-section-template') and '__main' in tag.get('id', ''))
print(f"Found {len(main_sections)} main section tags:")
for s in main_sections:
    print(f"ID: {s.get('id')}, classes: {s.get('class')}")

print("\n=== CHECKING PRODUCT VARIANTS / OPTIONS ===")
variants = soup.find_all(['product-variants', 'product-variant', 'variant-selects', 'fieldset'])
print(f"Variant elements count: {len(variants)}")
for v in variants:
    print(f"Tag: {v.name}, class: {v.get('class')}, id: {v.get('id')}")

# Check options inside product-variants
for opt in soup.select('.product-variant, .product-variants, [data-js-product-variants]'):
    print("\n--- OPTION BLOCK ---")
    print(opt.prettify()[:1000])

print("\n=== CHECKING QUANTITY SELECTOR ===")
qty = soup.select('.product-quantity, [name="quantity"], .quantity-selector, product-quantity')
print(f"Quantity elements count: {len(qty)}")
for q in qty:
    print(q.prettify()[:300])

