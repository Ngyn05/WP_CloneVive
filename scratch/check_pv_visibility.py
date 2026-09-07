import re
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

theme_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive"

with open(os.path.join(theme_dir, 'shop-us', 'products', 'vive-eagle', 'index.htm'), 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

print("--- Check product-variants in vive-eagle/index.htm ---")
pv_matches = list(re.finditer(r'<product-variants\b[\s\S]*?</product-variants>', html))
print(f"Found {len(pv_matches)} <product-variants> blocks:")
for m in pv_matches:
    print(m.group(0)[:500])

print("\n--- Check if product-variants is inside sidebar-widget or what parent ---")
for m in re.finditer(r'<product-variants\b', html):
    pos = m.start()
    print(html[max(0, pos-300):pos+300])

