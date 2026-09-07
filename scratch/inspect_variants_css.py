import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "https://shop-us.vive.com/cdn/shop/t/25/assets/component-product-variants.css?v=79163495057099558791783018041"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    css = resp.read().decode('utf-8', errors='ignore')

print(f"Variants CSS len: {len(css)}")
print(css)
