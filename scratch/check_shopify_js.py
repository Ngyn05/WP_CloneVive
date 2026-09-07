import re
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Search inside Shopify JS assets
js_urls = [
    "http://shop-us.vive.com/cdn/shop/t/25/assets/component-product-variants.js",
    "http://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.js",
    "http://shop-us.vive.com/cdn/shop/t/25/assets/global.js"
]

for url in js_urls:
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=10) as resp:
            js = resp.read().decode('utf-8', errors='ignore')
            print(f"URL: {url} -> len {len(js)}")
            if "hide-variants" in js:
                print(" -> FOUND hide-variants in", url)
            if "no-js-hidden" in js:
                print(" -> FOUND no-js-hidden in", url)
            for m in re.finditer(r'hide-variants|data-hide-variants|hidden', js):
                pos = m.start()
                print("    Match snippet:", js[max(0, pos-50):pos+100])
    except Exception as e:
        print("Error fetching", url, e)

