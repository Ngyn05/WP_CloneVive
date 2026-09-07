import re
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Search inside section-main-product.css
css_url = "https://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.css?v=20470544323851178941783018041"
req = urllib.request.Request(css_url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    css = resp.read().decode('utf-8', errors='ignore')

print(f"CSS len: {len(css)}")
for term in ['sidebar-widget', 'product-variant', 'product-variants', 'product-style', 'no-js-hidden']:
    print(f"\n--- MATCHES FOR {term} ---")
    for m in re.finditer(rf'[^{{;}}]*\b{term}\b[^{{;}}]*\{{[^}}]*\}}', css):
        print(m.group(0)[:200])

