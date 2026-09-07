import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/product/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8')

print("Page title:", re.search(r'<title>(.*?)</title>', html, re.I).group(1))

# Find all product cards or product sections rendered
product_sections = re.findall(r'<div\b[^>]*class=[\'"][^\'"]*(?:product-card|product-item|product-showcase|c-product|col-)[^\'"]*[\'"][^>]*>.*?(?:VIVE|Vive|Focus|Elite|Pro|Eagle).*?</div>', html, re.I | re.DOTALL)
print("Found potential product cards:", len(product_sections))

# Check for product names in html
names = ['VIVE Eagle', 'VIVE Focus Vision', 'VIVE XR Elite', 'VIVE Pro 2', 'VIVE Flow', 'VIVE Cosmos', 'VIVE Focus 3']
for name in names:
    count = len(re.findall(re.escape(name), html, re.I))
    print(f"Occurrence of '{name}': {count}")

# Check Buy Now buttons / links
buy_links = re.findall(r'<a\b[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)</a>', html, re.I | re.DOTALL)
print("\nProduct action links on /product/:")
for u, txt in buy_links:
    clean_txt = re.sub(r'<[^>]+>', '', txt).strip()
    if any(k in clean_txt.lower() for k in ['mua', 'buy', 'tìm hiểu', 'chi tiết', 'thêm', 'tell me', 'khám phá', 'xem']):
        print(f" - [{clean_txt}] -> {u}")
