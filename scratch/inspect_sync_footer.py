import urllib.request
from bs4 import BeautifulSoup
import json

html = urllib.request.urlopen('http://vive-vn.local/sync/').read().decode('utf-8', errors='ignore')
soup = BeautifulSoup(html, 'html.parser')

footers = []
for el in soup.find_all(attrs={'class': True}):
    classes = ' '.join(el.get('class', []))
    if 'footer' in classes.lower():
        footers.append({
            'tag': el.name,
            'class': classes,
            'html': str(el)[:500],
            'text': el.get_text(strip=True)[:200]
        })

with open('scratch/sync_footers.json', 'w', encoding='utf-8') as f:
    json.dump(footers, f, ensure_ascii=False, indent=2)

print("Saved sync_footers.json")
