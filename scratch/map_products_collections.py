import json
import glob
import os
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/all_products.json', 'r', encoding='utf-8') as f:
    all_products = json.load(f)

print(f"Total products in all_products.json: {len(all_products)}")

# Parse collection files to get exact product handles
collections_map = {}
for cf in glob.glob('shop-us/collections/*/index.htm'):
    handle = os.path.basename(os.path.dirname(cf))
    if handle in ['index.htm', 'collections_list.json']:
        continue
    with open(cf, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()
    
    # Extract title
    title_match = re.search(r'<h1\b[^>]*>(.*?)</h1>', html, re.I | re.S)
    title = re.sub(r'<[^>]+>', '', title_match.group(1)).strip() if title_match else handle
    
    # Find all product links
    # Look for /products/slug
    products = set(re.findall(r'/products/([a-zA-Z0-9_-]+)', html))
    collections_map[handle] = {
        'handle': handle,
        'title': title,
        'products': products
    }

# Also let's check product tags and product_type from Shopify data
product_to_cats = {}
for p in all_products:
    handle = p.get('handle')
    assigned = []
    for c_handle, c_info in collections_map.items():
        if c_handle in ['all']:
            continue
        if handle in c_info['products']:
            assigned.append(c_info['title'])
    
    # Fallback to product_type or tags if not in any specific collection
    if not assigned:
        p_type = p.get('product_type') or 'Phụ kiện VIVE'
        assigned.append(p_type)
        
    product_to_cats[handle] = assigned

print("\nSample Product => Categories mapping:")
for h, cats in list(product_to_cats.items())[:15]:
    print(f"- {h}: {cats}")

# How many products have at least 1 category
print(f"\nTotal mapped: {len(product_to_cats)}")
