import os
import json
import re
import urllib.request

BASE_URL = "https://shop-us.vive.com"
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

handles = [
    "vive-eagle-square-transition-lens-black",
    "vive-eagle-square-sunglasses-berry",
    "vive-eagle",
    "vive-eagle-square-clear-coffee",
    "vive-eagle-pantos-sunglasses-navy",
    "vive-eagle-square-clear-black",
    "vive-eagle-pantos-clear-light-brown",
    "vive-eagle-pantos-sunglasses-light-brown",
    "vive-eagle-pantos-sunglasses-matte-black",
    "vive-eagle-glossy-black-square-sun",
    "htc-vive-eagle-pantos-clear-lens-charcoal-black",
]

theme_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive"
products_dir = os.path.join(theme_dir, "shop-us", "products")
cache_file = os.path.join(theme_dir, "translation_cache.json")

translations = {}
if os.path.exists(cache_file):
    with open(cache_file, 'r', encoding='utf-8') as f:
        translations = json.load(f)

# Sort translations by length descending
sorted_trans = sorted(translations.items(), key=lambda x: len(x[0]), reverse=True)

for handle in handles:
    url = f"{BASE_URL}/products/{handle}"
    print(f"Fetching {url}...")
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            html = resp.read().decode('utf-8', errors='ignore')
            
            # Translate text
            for src, dst in sorted_trans:
                if len(src) < 3:
                    continue
                html = html.replace(src, dst)
            
            target_file = os.path.join(products_dir, handle, "index.htm")
            os.makedirs(os.path.dirname(target_file), exist_ok=True)
            with open(target_file, "w", encoding="utf-8") as f:
                f.write(html)
            print(f"Saved {target_file} ({len(html)} chars)")
    except Exception as e:
        print(f"Error for {handle}: {e}")

print("Done recrawling 11 products.")
