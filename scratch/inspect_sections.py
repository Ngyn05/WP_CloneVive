import re
import urllib.request

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products/21071655/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

sections = re.findall(r'<div\s+id=[\'"](shopify-section-[^\'"]+)[\'"]', html)
print('Template Sections in base file:')
for s in sections:
    print(' -', s)
