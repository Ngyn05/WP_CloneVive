import urllib.request
from bs4 import BeautifulSoup
import json

html = urllib.request.urlopen('http://vive-vn.local/arts/').read().decode('utf-8', errors='ignore')
soup = BeautifulSoup(html, 'html.parser')

data = {
    'title': soup.title.string if soup.title else 'No Title',
    'headings': [h.get_text(strip=True) for h in soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5'])],
    'paragraphs': [p.get_text(strip=True) for p in soup.find_all('p')[:5]]
}

with open('scratch/arts_translated_verify.json', 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=2)

print("Saved arts translated verify.")
