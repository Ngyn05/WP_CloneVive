import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

req = urllib.request.Request("https://shop-us.vive.com/products/vive-eagle", headers=HEADERS)
with urllib.request.urlopen(req) as resp:
    base_html = resp.read().decode('utf-8', errors='ignore')

# Check what options base_html has
for opt in re.findall(r'<div\b[^>]*class=["\'][^"\']*product-variant[^"\']*["\'][^>]*>[\s\S]*?</div>\s*</div>', base_html):
    print("--- Option in base_html ---")
    print(opt[:300])

# Check quantity in base_html
for q in re.findall(r'<product-quantity[\s\S]*?</product-quantity>', base_html):
    print("--- Quantity in base_html ---")
    print(q)
