import re
from bs4 import BeautifulSoup
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Check show-block-if-variant-selected
matches = re.findall(r'<[^>]*class=["\'][^"\']*show-block-if-variant-selected[^"\']*["\'][^>]*>', html)
print("Occurrences of show-block-if-variant-selected in index.htm:", len(matches))
for m in matches:
    print(m)

