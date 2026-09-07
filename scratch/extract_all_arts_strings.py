from bs4 import BeautifulSoup
import json

file_path = 'footer-sites/arts.vive.com/us/index.htm'
with open(file_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

all_strings = set()
for el in soup.find_all(text=True):
    t = el.strip()
    if t and len(t) > 2 and not t.startswith('{') and not t.startswith('function') and not t.startswith('var') and not t.startswith('window.'):
        all_strings.add(t)

with open('scratch/all_arts_strings.json', 'w', encoding='utf-8') as f:
    json.dump(sorted(list(all_strings)), f, ensure_ascii=False, indent=2)

print(f"Total unique strings: {len(all_strings)}")
