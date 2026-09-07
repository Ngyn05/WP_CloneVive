import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

scripts = re.findall(r'<script\b[^>]*src=[\'"]([^\'"]+)[\'"]', html)
print(f"Found {len(scripts)} external scripts:")
for s in scripts:
    print(" -", s)
