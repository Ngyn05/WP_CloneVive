import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

for m in re.finditer(r'189g?', html, re.I):
    start = max(0, m.start() - 300)
    end = min(len(html), m.end() + 300)
    print("--- MATCH ---")
    print(html[start:end])
