import json
import glob
import os
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/all_products.json', 'r', encoding='utf-8') as f:
    all_products = json.load(f)

print(f"Total products: {len(all_products)}")

images_found_locally = 0
images_online = 0

for p in all_products:
    imgs = p.get('images', [])
    if imgs and imgs[0].get('src'):
        src = imgs[0]['src']
        # check if local
        # e.g. shop-us/cdn/shop/files/...
        filename = os.path.basename(src.split('?')[0])
        # search in local shop-us or assets
        local_matches = glob.glob(f'shop-us/**/{filename}', recursive=True)
        if not local_matches:
            local_matches = glob.glob(f'assets/**/{filename}', recursive=True)
            
        if local_matches:
            images_found_locally += 1
        else:
            images_online += 1

print(f"Images found locally on disk: {images_found_locally}")
print(f"Images online (can be downloaded): {images_online}")
