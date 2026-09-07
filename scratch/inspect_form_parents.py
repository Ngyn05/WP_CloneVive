import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

forms = soup.find_all(['product-form', 'form'])
print(f"Found {len(forms)} form / product-form tags:")
for i, f in enumerate(forms):
    parent_classes = [p.get('class') for p in f.parents if p.name != '[document]']
    print(f"\nForm {i+1}: <{f.name} id='{f.get('id')}' class='{f.get('class')}'>")
    print("  Immediate Parent:", f.parent.name, f.parent.get('class'), f.parent.get('id'))
    print("  Ancestor path:", " > ".join([str(p.get('class')) for p in list(f.parents)[:4]]))

