import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products_vi.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for p in data:
    title = p.get('title', '')
    handle = p.get('handle', '')
    if 'kèm tay cầm' in title.lower() or 'controller' in handle.lower() or 'tay cầm' in title.lower():
        print(f"Handle: {handle} | Title: {title}")
