import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

urls = [
    'http://vive-vn.local/shop/products/21046855/',
    'http://vive-vn.local/shop/products/test/',
    'http://vive-vn.local/shop/products/vive-focus-vision-pc-vr-wired-bundle/',
    'http://vive-vn.local/shop/products/xr-elite-with-deluxe-pack-business-edition/'
]

for url in urls:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req) as resp:
            html = resp.read().decode('utf-8')
        titles = re.findall(r'<h1[^>]*>([\s\S]*?)</h1>', html)
        clean_titles = [re.sub(r'\s+', ' ', t).strip() for t in titles]
        has_zalo = 'TƯ VẤN QUA ZALO' in html
        has_buy = 'MUA NGAY' in html
        has_callback = 'Hãy để lại số điện thoại' in html
        print(f"\nURL: {url}")
        print(f"Status: 200 OK | H1 Count: {len(titles)} -> {clean_titles}")
        print(f"Zalo: {has_zalo} | Buy: {has_buy} | Callback Form: {has_callback}")
    except Exception as e:
        print(f"\nURL: {url} -> ERROR: {e}")
