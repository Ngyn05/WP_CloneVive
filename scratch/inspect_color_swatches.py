import urllib.request
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
}

req = urllib.request.Request("https://shop-us.vive.com/products/vive-eagle", headers=HEADERS)
with urllib.request.urlopen(req) as resp:
    base_html = resp.read().decode('utf-8', errors='ignore')

soup = BeautifulSoup(base_html, 'html.parser')

color_div = soup.select_one('[data-name*="color"]')
print("=== COLOR DIV IN BASE HTML ===")
if color_div:
    print(color_div.prettify())
else:
    print("Color div not found!")

# Check all styles or scripts relating to product-variant__item-swatch
swatches = soup.select('.product-variant__item-swatch')
print(f"\nFound {len(swatches)} swatches:")
for s in swatches:
    print(s.parent.prettify())
