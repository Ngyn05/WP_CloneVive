import urllib.request
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

# 1. Fetch live shop-us.vive.com/products/vive-eagle
print("Fetching live https://shop-us.vive.com/products/vive-eagle ...")
req_live = urllib.request.Request("https://shop-us.vive.com/products/vive-eagle", headers=HEADERS)
with urllib.request.urlopen(req_live, timeout=30) as resp:
    live_html = resp.read().decode('utf-8', errors='ignore')

# 2. Fetch local http://vive-vn.local/shop/products/vive-eagle/
print("Fetching local http://vive-vn.local/shop/products/vive-eagle/ ...")
req_local = urllib.request.Request("http://vive-vn.local/shop/products/vive-eagle/", headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req_local, timeout=30) as resp:
    local_html = resp.read().decode('utf-8', errors='ignore')

print("\n==========================================")
print(f"LIVE HTML Length: {len(live_html)} bytes")
print(f"LOCAL HTML Length: {len(local_html)} bytes")

# Check sections in live vs local
live_sections = re.findall(r'id=["\']shopify-section-([^"\']+)["\']', live_html)
local_sections = re.findall(r'id=["\']shopify-section-([^"\']+)["\']', local_html)

print("\n--- SECTIONS IN LIVE PAGE ---")
for s in live_sections:
    print(f" - {s}")

print("\n--- SECTIONS IN LOCAL PAGE ---")
for s in local_sections:
    print(f" - {s}")

# Check Section main content
print("\n--- VARIANT SELECTORS ---")
print("Live has product-variants:", "product-variants" in live_html)
print("Local has product-variants:", "product-variants" in local_html)

# Check all marketing sections / rich content below
print("\n--- RICH SECTIONS / MARKETING CONTENT ---")
marketing_keywords = ['qualcomm', '12mp', 'snapdragon', 'zeiss', 'translation', 'ai-notes', 'wearable', 'specs', 'faq']
for kw in marketing_keywords:
    print(f"Keyword '{kw}': Live={kw in live_html.lower()} | Local={kw in local_html.lower()}")

