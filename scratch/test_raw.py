with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    raw_html = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(raw_html, 'html.parser')
page = soup.find('product-page')
print('Raw HTML Product-page direct children:')
for c in page.find_all(recursive=False):
    print(f"  <{c.name}> class={c.get('class')}")
