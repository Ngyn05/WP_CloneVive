import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "http://vive-vn.local/shop/products/vive-eagle/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

# Find product-text
pos = html.find('class="product-text')
if pos != -1:
    print(html[pos:pos+4000])
else:
    print("product-text NOT found!")
