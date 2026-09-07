import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "http://vive-vn.local/shop/collections/vive-eagle-out-now/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

print("=== TITLE ===")
for t in re.findall(r'<title>.*?</title>', html):
    print(t)

print("\n=== H1 / H2 ===")
for h in re.findall(r'<h[123]\b[^>]*>.*?</h[123]>', html)[:10]:
    print(h)

print("\n=== HAS BREADCRUMB? ===")
for b in re.findall(r'<nav\b[^>]*breadcrumb[^>]*>.*?</nav>|<div\b[^>]*breadcrumb[^>]*>.*?</div>', html, re.I):
    print(b[:300])

print("\n=== HAS 'Trang chủ / Danh mục sản phẩm' ===")
for m in re.finditer(r'Danh mục sản phẩm|Kính AI Vive Eagle', html):
    start = max(0, m.start() - 100)
    end = min(len(html), m.end() + 100)
    print(html[start:end])
