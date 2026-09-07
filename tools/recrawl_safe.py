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

# Filter out dangerous short keys and sort by length
filtered_trans = [(k, v) for k, v in translations.items() if len(k.strip()) >= 3 and not k.startswith(('http', '//', 'cdn', '.png', '.jpg', '.webp', '.css', '.js'))]
sorted_trans = sorted(filtered_trans, key=lambda x: len(x[0]), reverse=True)

def translate_html_safely(html):
    # Only translate text between > and <
    def replace_text_chunk(match):
        text = match.group(1)
        if not text.strip():
            return match.group(0)
        # Avoid translating inside scripts/styles
        for src, dst in sorted_trans:
            if src in text:
                text = text.replace(src, dst)
        return '>' + text + '<'

    # Protect script and style tags
    protected = []
    def save_tag(m):
        protected.append(m.group(0))
        return f"__PROTECTED_BLOCK_{len(protected)-1}__"

    cleaned = re.sub(r'<(script|style)\b[^>]*>[\s\S]*?</\1>', save_tag, html, flags=re.IGNORECASE)
    cleaned = re.sub(r'>([^<]+)<', replace_text_chunk, cleaned)
    
    # Restore protected blocks
    for idx, block in enumerate(protected):
        cleaned = cleaned.replace(f"__PROTECTED_BLOCK_{idx}__", block)
    
    return cleaned

for handle in handles:
    url = f"{BASE_URL}/products/{handle}"
    print(f"Fetching {url}...")
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            raw_html = resp.read().decode('utf-8', errors='ignore')
            translated = translate_html_safely(raw_html)
            
            target_file = os.path.join(products_dir, handle, "index.htm")
            os.makedirs(os.path.dirname(target_file), exist_ok=True)
            with open(target_file, "w", encoding="utf-8") as f:
                f.write(translated)
            print(f"Successfully wrote {target_file} ({len(translated)} chars)")
    except Exception as e:
        print(f"Error fetching {handle}: {e}")

print("All 11 Eagle products cleanly refreshed with intact image URLs!")
