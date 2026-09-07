import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/collections/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

cards = soup.find_all('div', class_=re.compile(r'card|collection-item|grid-item', re.I))
print("Total card elements:", len(cards))

collection_links = soup.find_all('a', href=re.compile(r'/collections/[^/]+/?$'))
seen = set()
collections_list = []

for a in collection_links:
    href = a.get('href')
    # get text or title
    title = a.get_text(strip=True)
    img = a.find('img')
    img_src = img.get('src') or img.get('data-src') if img else ''
    
    # Clean handle
    handle = href.strip('/').split('/')[-1]
    if handle not in seen and handle not in ['collections', 'all', 'vendors']:
        seen.add(handle)
        collections_list.append({
            'handle': handle,
            'url': href,
            'title': title,
            'image': img_src
        })

print("\nCollections found on /shop/collections/ page:")
for c in collections_list:
    print(f"- {c['handle']}: '{c['title']}' (image: {c['image'][:60] if c['image'] else 'None'})")
