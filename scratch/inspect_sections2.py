import re

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products/21071655/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's find id="shopify-section..." or class="shopify-section"
matches = re.findall(r'<div[^>]*class="[^"]*shopify-section[^"]*"[^>]*>', html)
print(f"Total shopify-section divs: {len(matches)}")
for m in matches:
    print(m)
