from bs4 import BeautifulSoup

file_path = 'footer-sites/arts.vive.com/us/index.htm'
with open(file_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

texts = []
for el in soup.find_all(['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'a', 'span', 'button']):
    t = el.get_text(strip=True)
    if t and len(t) > 3:
        texts.append(t)

print(f"Total extracted text elements: {len(texts)}")
unique_texts = list(dict.fromkeys(texts))
print(f"Unique strings: {len(unique_texts)}")
for s in unique_texts[:40]:
    print('-', s)
