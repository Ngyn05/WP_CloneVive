import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products_vi.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for p in data:
    title = p.get('title', '')
    handle = p.get('handle', '')
    sku = p.get('sku', '')
    if 'temple' in handle or 'cặp' in title or 'đệm' in title or 'gọng' in title or '99H' in sku:
        print(f"Handle: {handle} | SKU: {sku} | Title: {title}")
