import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

pages = [m.start() for m in re.finditer(r'<product-page\b', html)]
print(f"Occurrences of <product-page>: {len(pages)}")
for i, pos in enumerate(pages):
    print(f"Occurrence {i+1} at {pos}:")
    print(html[pos:pos+250])
