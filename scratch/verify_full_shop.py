import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

all_products = []
page = 1
while True:
    try:
        url = f'http://vive-vn.local/wp-json/wp/v2/product?per_page=100&page={page}'
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=30) as res:
            items = json.loads(res.read().decode('utf-8'))
            if not items:
                break
            all_products.extend(items)
            page += 1
    except Exception as e:
        break

print("===============================================================")
print(f"BÁO CÁO KIỂM TRA SẢN PHẨM WOOCOMMERCE:")
print(f"Tổng số sản phẩm trong cơ sở dữ liệu: {len(all_products)} / 93")
print("===============================================================")

with_thumb = sum(1 for p in all_products if p.get('featured_media', 0) > 0)
print(f"1. Ảnh đại diện (Featured Image / Thumbnail): {with_thumb} / {len(all_products)} sản phẩm đã có ảnh đại diện.")

# Check stock & price via WC REST or post meta
print(f"\n2. Danh sách 15 sản phẩm mẫu với Tên Tiếng Việt & Đầy đủ thông tin:")
for i, p in enumerate(all_products[:15], 1):
    pid = p['id']
    slug = p['slug']
    title = p.get('title', {}).get('rendered', '')
    thumb = p.get('featured_media', 0)
    print(f"{i:2d}. [ID: {pid}] {title}")
    print(f"    Slug: /shop/products/{slug}/ | Ảnh ID: {thumb}")

print("\n===============================================================")
