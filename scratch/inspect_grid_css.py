import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Search for main-product and grid layout in theme.css and section-main-product.css
for name, url in [
    ("theme.css", "https://shop-us.vive.com/cdn/shop/t/25/assets/theme.css?v=105739051976567433851783018041"),
    ("section-main-product.css", "https://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.css?v=20470544323851178941783018041")
]:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as resp:
        css = resp.read().decode('utf-8', errors='ignore')
    
    print(f"\n=================== {name} ===================")
    for sel in ['main-product', 'product-text', 'product-gallery', 'grid-portable-5', 'grid-3', 'grid--layout']:
        matches = list(re.finditer(rf'[^{{;}}]*\b{sel}\b[^{{;}}]*\{{[^}}]*\}}', css))
        if matches:
            print(f"Found {len(matches)} rules for {sel}:")
            for m in matches[:5]:
                print("  ", m.group(0).strip()[:150])

