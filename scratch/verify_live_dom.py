import urllib.request
from bs4 import BeautifulSoup

url = 'http://vive-vn.local/shop/products/vive-eagle/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    resp = urllib.request.urlopen(req)
    html = resp.read().decode('utf-8')
    
    soup = BeautifulSoup(html, 'html.parser')
    page = soup.find('product-page')
    print("Page direct children count:", len(page.find_all(recursive=False)))
    for c in page.find_all(recursive=False):
        print(f"  <{c.name}> class={c.get('class')}")
    
    p_text = soup.find(class_='product-text')
    if p_text:
        print("\nproduct-text direct children:")
        for c in p_text.find_all(recursive=False):
            print(f"    <{c.name}> class={c.get('class')}")
        print("\nIs product-variants inside product-text?", p_text.find('product-variants') is not None)
        print("Is product-actions inside product-text?", p_text.find(class_='product-actions') is not None)
        print("Is 3 buttons inside product-text?", p_text.find(class_='vive-woo-product__actions') is not None)
        print("Is quantity stepper inside product-text?", p_text.find(class_='product-quantity') is not None)
except Exception as e:
    print("Error:", e)
