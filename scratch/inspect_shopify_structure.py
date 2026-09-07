import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

# Fetch base page
req = urllib.request.Request("https://shop-us.vive.com/products/vive-eagle", headers=HEADERS)
with urllib.request.urlopen(req) as resp:
    base_html = resp.read().decode('utf-8', errors='ignore')

# Fetch section
req_sec = urllib.request.Request("https://shop-us.vive.com/products/vive-eagle?section_id=template--27611584627005__main", headers=HEADERS)
with urllib.request.urlopen(req_sec) as resp_sec:
    sec_html = resp_sec.read().decode('utf-8', errors='ignore')

print("Base HTML length:", len(base_html))
print("Section HTML length:", len(sec_html))

# Where is the section in base_html?
m = re.search(r'<div\b[^>]*id=["\']shopify-section-template--27611584627005__main["\'][^>]*>([\s\S]*?)</div>\s*<div\b[^>]*id=["\']shopify-section-template--27611584627005__rich_text', base_html)
if m:
    print("Found section in base_html, inner length:", len(m.group(1)))
else:
    print("Could not match between main and rich_text")

