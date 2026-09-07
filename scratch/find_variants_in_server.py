import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "http://vive-vn.local/shop/products/vive-eagle/"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8', errors='ignore')

pos = html.find('Chọn các biến thể')
print("Position of 'Chọn các biến thể':", pos)
if pos != -1:
    print(html[pos-200:pos+1500])
else:
    print("NOT FOUND!")
