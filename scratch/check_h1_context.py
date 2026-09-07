import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products/21046855/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

for m in re.finditer(r'<h1[\s\S]*?</h1>', html):
    start = max(0, m.start() - 300)
    end = min(len(html), m.end() + 300)
    print("--- H1 CONTEXT ---")
    print(html[start:end])
