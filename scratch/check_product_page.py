import re

with open('pages/us/product/index.htm', encoding='utf-8') as f:
    html = f.read()

print('Length of pages/us/product/index.htm:', len(html))

# Search for headings and product names
h2_tags = re.findall(r'<h[1-4]\b[^>]*>(.*?)</h[1-4]>', html, re.I | re.DOTALL)
print('Headings in index.htm:')
for h in h2_tags:
    clean_h = re.sub(r'<[^>]+>', '', h).strip()
    if clean_h:
        print(' -', clean_h)

# Check all product links in index.htm
links = re.findall(r'<a\b[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)</a>', html, re.I | re.DOTALL)
print('\nLinks in index.htm:')
for u, txt in links:
    clean_txt = re.sub(r'<[^>]+>', '', txt).strip()
    if 'product' in u or 'vive' in u or 'shop' in u:
        print(f' {clean_txt} -> {u}')
