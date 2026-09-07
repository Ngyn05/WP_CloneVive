import os
import json
import re
import urllib.request

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

def update_product_with_full_section(handle):
    page_url = f"{BASE_URL}/products/{handle}"
    req = urllib.request.Request(page_url, headers=HEADERS)
    with urllib.request.urlopen(req, timeout=30) as resp:
        page_html = resp.read().decode('utf-8', errors='ignore')

    # Find main section id
    m = re.search(r'id=["\']shopify-section-(template--\d+__main)["\']', page_html)
    section_id = m.group(1) if m else "template--27611584627005__main"
    
    section_url = f"{BASE_URL}/products/{handle}?section_id={section_id}"
    req_sec = urllib.request.Request(section_url, headers=HEADERS)
    try:
        with urllib.request.urlopen(req_sec, timeout=30) as resp_sec:
            section_html = resp_sec.read().decode('utf-8', errors='ignore')
    except Exception as e:
        print(f"Error fetching section for {handle}: {e}")
        section_html = None

    if section_html and f'id="shopify-section-{section_id}"' in page_html:
        # Replace the empty section container with the complete rendered section HTML
        pattern = re.compile(rf'<div\b[^>]*id=["\']shopify-section-{section_id}["\'][^>]*>[\s\S]*?</div>(?=\s*<div\b|\s*<footer|\s*</main|\s*<section)', re.IGNORECASE)
        replacement = f'<div id="shopify-section-{section_id}" class="shopify-section shopify-section--main-product">\n{section_html}\n</div>'
        page_html = pattern.sub(replacement, page_html, count=1)

    translated = translate_html_safely(page_html)
    
    target_file = os.path.join(products_dir, handle, "index.htm")
    os.makedirs(os.path.dirname(target_file), exist_ok=True)
    with open(target_file, "w", encoding="utf-8") as f:
        f.write(translated)
    print(f"Updated {handle} with full variants section! Size: {len(translated)} chars")

update_product_with_full_section("vive-eagle")
