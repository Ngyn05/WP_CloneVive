import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Query all products from WP REST API
page = 1
all_db_products = []
while True:
    try:
        url = f'http://vive-vn.local/wp-json/wp/v2/product?per_page=100&page={page}&_fields=id,slug,name,categories'
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=10)
        data = json.loads(res.read().decode('utf-8'))
        if not data:
            break
        all_db_products.extend(data)
        page += 1
    except Exception as e:
        break

print(f"Total products currently in Database: {len(all_db_products)}")

# Check for duplicates or non-shop products
slug_counts = {}
for p in all_db_products:
    slug = p['slug']
    slug_counts[slug] = slug_counts.get(slug, 0) + 1

duplicates = {s: c for s, c in slug_counts.items() if c > 1}
print("Duplicate slugs in DB:", duplicates)

# Query all categories
req = urllib.request.Request('http://vive-vn.local/wp-json/wp/v2/product_cat?per_page=100', headers={'User-Agent': 'Mozilla/5.0'})
res = urllib.request.urlopen(req, timeout=10)
cats = json.loads(res.read().decode('utf-8'))

print(f"\nTotal categories in DB: {len(cats)}")
for c in cats:
    print(f"- [ID: {c['id']}] '{c['name']}' (slug: {c['slug']}) => {c['count']} items")
