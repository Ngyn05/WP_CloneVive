import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/shop/products/vive-eagle/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req, timeout=10).read().decode('utf-8')

idx = html.find('class="product-text')
print(html[idx+2000:idx+8000])
