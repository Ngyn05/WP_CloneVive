import re
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Check section-main-product.css and theme.css
for name, url in [
    ("section-main-product.css", "https://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.css?v=20470544323851178941783018041"),
    ("theme.css", "https://shop-us.vive.com/cdn/shop/t/25/assets/theme.css?v=105739051976567433851783018041"),
    ("component-sidebar.css", "https://shop-us.vive.com/cdn/shop/t/25/assets/component-sidebar.css?v=166025024783731078101783018041")
]:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as resp:
        css = resp.read().decode('utf-8', errors='ignore')
    
    print(f"\n=================== {name} ===================")
    for sel in ['sidebar-widget', 'sidebar', 'product-actions', 'hide-on-desk', 'hide-on-lap', 'element--hide-on-lap', 'element--hide-on-desk', 'portable']:
        matches = list(re.finditer(rf'[^{{;}}]*\b{sel}\b[^{{;}}]*\{{[^}}]*\}}', css))
        if matches:
            print(f"Found {len(matches)} rules for {sel}:")
            for m in matches[:5]:
                print("  ", m.group(0).strip()[:150])

