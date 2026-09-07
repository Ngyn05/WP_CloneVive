import os
import json
import re
import urllib.request
import time
import sys

sys.stdout.reconfigure(encoding='utf-8')

BASE_URL = "https://shop-us.vive.com"
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

theme_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive"
products_dir = os.path.join(theme_dir, "shop-us", "products")
cache_file = os.path.join(theme_dir, "translation_cache.json")

translations = {}
if os.path.exists(cache_file):
    with open(cache_file, 'r', encoding='utf-8') as f:
        translations = json.load(f)

filtered_trans = [(k, v) for k, v in translations.items() if len(k.strip()) >= 3 and not k.startswith(('http', '//', 'cdn', '.png', '.jpg', '.webp', '.css', '.js'))]
sorted_trans = sorted(filtered_trans, key=lambda x: len(x[0]), reverse=True)

def translate_html_safely(html):
    def replace_text_chunk(match):
        text = match.group(1)
        if not text.strip():
            return match.group(0)
        for src, dst in sorted_trans:
            if src in text:
                text = text.replace(src, dst)
        return '>' + text + '<'

    protected = []
    def save_tag(m):
        protected.append(m.group(0))
        return f"__PROTECTED_BLOCK_{len(protected)-1}__"

    cleaned = re.sub(r'<(script|style)\b[^>]*>[\s\S]*?</\1>', save_tag, html, flags=re.IGNORECASE)
    cleaned = re.sub(r'>([^<]+)<', replace_text_chunk, cleaned)
    for idx, block in enumerate(protected):
        cleaned = cleaned.replace(f"__PROTECTED_BLOCK_{idx}__", block)
    return cleaned

handles = [d for d in os.listdir(products_dir) if os.path.isdir(os.path.join(products_dir, d))]
print(f"Processing {len(handles)} products cleanly...")

for idx, handle in enumerate(handles, 1):
    try:
        page_url = f"{BASE_URL}/products/{handle}"
        req = urllib.request.Request(page_url, headers=HEADERS)
        with urllib.request.urlopen(req, timeout=30) as resp:
            page_html = resp.read().decode('utf-8', errors='ignore')

        m = re.search(r'id=["\']shopify-section-(template--\d+__main)["\']', page_html)
        section_id = m.group(1) if m else None
        
        if section_id:
            section_url = f"{BASE_URL}/products/{handle}?section_id={section_id}"
            req_sec = urllib.request.Request(section_url, headers=HEADERS)
            try:
                with urllib.request.urlopen(req_sec, timeout=30) as resp_sec:
                    section_html = resp_sec.read().decode('utf-8', errors='ignore')
                    if section_html and f'id="shopify-section-{section_id}"' in page_html:
                        # Replace the initial empty section with section_html directly
                        pattern = re.compile(rf'<div\b[^>]*id=["\']shopify-section-{section_id}["\'][^>]*>[\s\S]*?</div>(?=\s*<div\b|\s*<footer|\s*</main|\s*<section)', re.IGNORECASE)
                        page_html = pattern.sub(section_html, page_html, count=1)
            except Exception as e:
                pass

        translated = translate_html_safely(page_html)
        target_file = os.path.join(products_dir, handle, "index.htm")
        with open(target_file, "w", encoding="utf-8") as f:
            f.write(translated)
        if idx % 10 == 0 or idx == len(handles):
            print(f"[{idx}/{len(handles)}] Processed {handle}")
    except Exception as e:
        print(f"[{idx}/{len(handles)}] Error on {handle}: {e}")

print("All 93 products cleanly inlined!")

