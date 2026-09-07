import re

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    c = f.read()

matches = [m.start() for m in re.finditer(r'id=[\'"]shopify-section-template--\d+__main[\'"]', c)]
print('Found instances of main section:', len(matches))
for pos in matches:
    print('Instance at:', pos, c[pos:pos+200])
