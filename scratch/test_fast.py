import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

urls = [
    'http://vive-vn.local/shop/products/21046855/',
    'http://vive-vn.local/shop/products/vive-focus-vision-pc-vr-wired-bundle/',
    'http://vive-vn.local/shop/products/controller-for-xr-elite-right-bulk-packaging/',
    'http://vive-vn.local/shop/collections/'
]

for url in urls:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            html = resp.read().decode('utf-8')
            titles = re.findall(r'<h1[^>]*>([\s\S]*?)</h1>', html)
            clean_titles = [re.sub(r'\s+', ' ', t).strip() for t in titles]
            print(f"URL: {url} -> Status: 200 | Titles: {clean_titles}")
    except Exception as e:
        print(f"URL: {url} -> Error: {e}")
