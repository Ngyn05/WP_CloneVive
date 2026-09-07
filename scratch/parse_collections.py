import glob
import re
import json
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

collections_dir = 'shop-us/collections'
collection_files = glob.glob(f'{collections_dir}/*/index.htm')

collections_data = {}

for cf in collection_files:
    handle = os.path.basename(os.path.dirname(cf))
    if handle in ['index.htm', 'collections_list.json']:
        continue
    
    with open(cf, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()
    
    # Extract title
    title_match = re.search(r'<h1\b[^>]*>(.*?)</h1>', html, re.I | re.S)
    title = re.sub(r'<[^>]+>', '', title_match.group(1)).strip() if title_match else handle
    
    # Extract all product handles/links in this collection
    # Links like /products/vive-eagle or /products/handle
    product_links = set(re.findall(r'/products/([a-zA-Z0-9_-]+)', html))
    
    collections_data[handle] = {
        'handle': handle,
        'title': title,
        'products': sorted(list(product_links))
    }
    print(f"Collection: {handle} => '{title}' ({len(product_links)} products)")

print(f"\nTotal Collections found: {len(collections_data)}")
