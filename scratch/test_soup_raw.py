with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re
from bs4 import BeautifulSoup

# Let's inspect lines 2270 to 2300 in raw html
lines = html.splitlines()[2270:2300]
print("\n".join(lines))

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("RAW PARSER direct children:", len(page.find_all(recursive=False)))
for c in page.find_all(recursive=False):
    print(f"  <{c.name}> class={c.get('class')}")
