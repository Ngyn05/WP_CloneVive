import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "https://shop-us.vive.com/cdn/shop/t/25/assets/section-main-product.js?v=10168746592286365731783018041"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    js = resp.read().decode('utf-8', errors='ignore')

print(js[2500:])
