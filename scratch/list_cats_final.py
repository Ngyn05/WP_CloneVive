import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Check products per category
req = urllib.request.Request('http://vive-vn.local/wp-json/wp/v2/product_cat?per_page=100', headers={'User-Agent': 'Mozilla/5.0'})
res = urllib.request.urlopen(req, timeout=10)
cats = json.loads(res.read().decode('utf-8'))

print("=== DANH MỤC SẢN PHẨM TRONG DATABASE ===")
for c in sorted(cats, key=lambda x: x['count'], reverse=True):
    if c['count'] > 0:
        print(f"• {c['name']} ({c['slug']}): {c['count']} sản phẩm")
