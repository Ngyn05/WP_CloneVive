import json
import urllib.request
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

os.makedirs('assets/media/products', exist_ok=True)

with open('shop-us/all_products.json', 'r', encoding='utf-8') as f:
    all_products = json.load(f)

downloaded = 0
failed = 0

for p in all_products:
    handle = p.get('handle')
    imgs = p.get('images', [])
    if not imgs or not imgs[0].get('src'):
        continue
    
    src = imgs[0]['src']
    if src.startswith('//'):
        src = 'https:' + src
    
    ext = os.path.splitext(src.split('?')[0])[1]
    if not ext:
        ext = '.png'
        
    target_path = os.path.join('assets/media/products', f'{handle}{ext}')
    
    if os.path.exists(target_path) and os.path.getsize(target_path) > 1000:
        downloaded += 1
        continue
        
    try:
        req = urllib.request.Request(src, headers={'User-Agent': 'Mozilla/5.0'})
        data = urllib.request.urlopen(req, timeout=15).read()
        with open(target_path, 'wb') as fp:
            fp.write(data)
        downloaded += 1
        print(f"Downloaded: {handle}{ext} ({len(data)} bytes)")
    except Exception as e:
        failed += 1
        print(f"Failed {handle}: {e}")

print(f"\nFinished: {downloaded} downloaded, {failed} failed out of {len(all_products)} products.")
