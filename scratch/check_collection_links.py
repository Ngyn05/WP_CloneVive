import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

collections = [
    'vive-flow',
    'vive-flow-for-business',
    'vive-xr-elite-for-business',
    'xr-series',
    'pro-series',
    'focus-series',
    'accessories'
]

for col in collections:
    url = f'http://vive-vn.local/shop/collections/{col}/'
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=5) as resp:
            html = resp.read().decode('utf-8')
        
        cards = re.findall(r'<div class="product-item"[\s\S]*?</div></div></div>', html)
        print(f"\nCollection: {col} ({len(cards)} products)")
        for card in cards[:3]:
            title_match = re.search(r'class="product-item__title"[^>]*href="([^"]+)"[^>]*>[\s\S]*?<span[^>]*>(.*?)</span>', card)
            img_match = re.search(r'class="product-item__image"[^>]*href="([^"]+)"', card)
            buy_match = re.search(r'class="button[^"]*"[^>]*href="([^"]+)"', card)
            if title_match:
                print(f" - Title: {title_match.group(2).strip()} -> Link: {title_match.group(1)}")
            if img_match:
                print(f"   Image Link: {img_match.group(1)}")
            if buy_match:
                print(f"   Buy Link: {buy_match.group(1)}")
    except Exception as e:
        print(f"Collection: {col} -> Error: {e}")
