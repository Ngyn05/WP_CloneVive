import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/all_products.json', 'r', encoding='utf-8') as f:
    shop_products = json.load(f)

shop_slugs = set(p['handle'] for p in shop_products)

# Query all products from DB
page = 1
db_products = []
while True:
    try:
        url = f'http://vive-vn.local/wp-json/wp/v2/product?per_page=100&page={page}'
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=10)
        data = json.loads(res.read().decode('utf-8'))
        if not data:
            break
        db_products.extend(data)
        page += 1
    except Exception as e:
        break

print(f"Shop handles in JSON: {len(shop_slugs)}")
print(f"DB products: {len(db_products)}")

extra = []
for p in db_products:
    if p['slug'] not in shop_slugs:
        title = p.get('title', {}).get('rendered', '')
        extra.append((p['id'], p['slug'], title))

print(f"\nExtra {len(extra)} products in DB (not in all_products.json):")
for pid, slug, title in extra:
    print(f"- [ID: {pid}] Slug: '{slug}', Title: '{title}'")
