import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

try:
    req = urllib.request.Request('http://vive-vn.local/wp-json/wp/v2/product_cat?per_page=100', headers={'User-Agent': 'Mozilla/5.0'})
    res = urllib.request.urlopen(req, timeout=10)
    data = json.loads(res.read().decode('utf-8'))
    print(f"Total categories in Database: {len(data)}")
    for c in data:
        print(f"- [ID: {c['id']}] {c['name']} (slug: {c['slug']}) => {c['count']} sản phẩm")
except Exception as e:
    print("Error querying product_cat:", e)
