import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

print("Elements with sidebar in class:")
for el in soup.find_all(class_=re.compile(r'sidebar', re.I)):
    print(f"<{el.name} class='{el.get('class')}'> id='{el.get('id')}'>")
