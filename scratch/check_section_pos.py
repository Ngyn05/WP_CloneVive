import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Find all occurrences of shopify-section-template
sections = [m.start() for m in re.finditer(r'<div\b[^>]*id=[\'"]shopify-section-template--\d+__main[\'"]', html)]
print(f"Number of occurrences: {len(sections)}")
for i, pos in enumerate(sections):
    print(f"\n--- OCCURRENCE {i+1} at index {pos} ---")
    print(html[pos:pos+400])

