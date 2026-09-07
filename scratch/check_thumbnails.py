import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Query products with 60s timeout
page = 1
with_thumb = []
without_thumb = []

while True:
    try:
        url = f'http://vive-vn.local/wp-json/wp/v2/product?per_page=100&page={page}'
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=60)
        data = json.loads(res.read().decode('utf-8'))
        if not data:
            break
        for p in data:
            pid = p['id']
            slug = p['slug']
            title = p.get('title', {}).get('rendered', slug)
            thumb = p.get('featured_media')
            if thumb and thumb > 0:
                with_thumb.append((pid, slug, title, thumb))
            else:
                without_thumb.append((pid, slug, title))
        page += 1
    except Exception as e:
        print("Done or error:", e)
        break

print(f"\n==========================================")
print(f"Products WITH Featured Image (Đã có Ảnh đại diện): {len(with_thumb)} / {len(with_thumb) + len(without_thumb)}")
print(f"Products WITHOUT Featured Image: {len(without_thumb)}")
print(f"==========================================")

if with_thumb:
    print("\nDanh sách sản phẩm mẫu đã có ảnh đại diện Media:")
    for pid, slug, title, thumb in with_thumb[:12]:
        print(f"• [ID: {pid}] {title} (Ảnh Media ID: {thumb})")
