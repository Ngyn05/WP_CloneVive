import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

links = re.findall(r'<link\b[^>]*href=[\'"]([^\'"]+)[\'"]', html)
print(f"Found {len(links)} stylesheets / links:")
for l in links:
    if "css" in l:
        print(" -", l)
