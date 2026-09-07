import urllib.request
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "http://vive-vn.local/shop/products/vive-eagle/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

soup = BeautifulSoup(html, 'html.parser')

product_text = soup.find(class_='product-text')
print("=== What is INSIDE .product-text ===")
if product_text:
    for c in product_text.children:
        if c.name:
            print(f"- <{c.name} class='{c.get('class')}'>")
else:
    print(".product-text not found")

print("\n=== What is OUTSIDE .product-text (siblings) ===")
if product_text:
    for sib in list(product_text.next_siblings)[:10]:
        if getattr(sib, 'name', None):
            print(f"- <{sib.name} class='{sib.get('class')}'>")
