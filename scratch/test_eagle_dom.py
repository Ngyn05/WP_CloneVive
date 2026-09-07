import urllib.request
from bs4 import BeautifulSoup

url = 'http://vive-vn.local/shop/products/vive-eagle/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
if page:
    print('Product-page children:')
    for c in page.find_all(recursive=False):
        print(f"  <{c.name}> class={c.get('class')} id={c.get('id')}")
