import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

matches = [m.start() for m in re.finditer(r'<div\b[^>]*class=[\'"][^\'"]*product-variants', html)]
print(f"Occurrences of product-variants in file: {len(matches)}")
for i, pos in enumerate(matches):
    print(f"\n=== MATCH {i+1} at index {pos} ===")
    print(html[pos:pos+500])
