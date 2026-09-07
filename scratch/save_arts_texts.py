from bs4 import BeautifulSoup
import json

file_path = 'footer-sites/arts.vive.com/us/index.htm'
with open(file_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

# Lay cac the p, h1, h2, h3, h4, h5, a trong container chinh
content_texts = []
for el in soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p']):
    t = el.get_text(strip=True)
    if t and len(t) > 5:
        content_texts.append(t)

unique_texts = list(dict.fromkeys(content_texts))
with open('scratch/arts_texts.json', 'w', encoding='utf-8') as f:
    json.dump(unique_texts, f, ensure_ascii=False, indent=2)

print(f"Saved {len(unique_texts)} content texts.")
