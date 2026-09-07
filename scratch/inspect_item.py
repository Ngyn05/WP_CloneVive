with open('shop-us/products/2104005/index.htm', 'r', encoding='utf-8') as f:
    raw = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(raw, 'html.parser')
page = soup.find('product-page')
print("RAW product-page direct children count:", len(page.find_all(recursive=False)))
for c in page.find_all(recursive=False):
    print(f"  <{c.name}> class={c.get('class')}")
