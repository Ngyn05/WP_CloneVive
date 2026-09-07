import re
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

css_files = [
    "https://shop-us.vive.com/cdn/shop/t/25/assets/theme.css?v=105739051976567433851783018041",
    "https://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.css?v=20470544323851178941783018041",
    "https://shop-us.vive.com/cdn/shop/t/25/assets/component-product-variants.css?v=79163495057099558791783018041"
]

for url in css_files:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    with urllib.request.urlopen(req) as resp:
        css = resp.read().decode('utf-8', errors='ignore')
    for m in re.finditer(r'[^{};]*no-js[^{};]*\{[^}]*\}', css):
        print(f"Match in {url}:", m.group(0))
    for m in re.finditer(r'[^{};]*product-variants[^{};]*\{[^}]*\}', css):
        print(f"Match in {url} for product-variants:", m.group(0))
