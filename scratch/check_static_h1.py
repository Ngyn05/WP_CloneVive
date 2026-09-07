import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products/21046855/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

h1s = re.findall(r'<h1[^>]*>[\s\S]*?</h1>', html)
print(f"Total <h1> in static file 21046855: {len(h1s)}")
for h in h1s:
    print(" -", h.strip())
