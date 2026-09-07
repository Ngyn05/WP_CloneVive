import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

urls = [
    'http://vive-vn.local/product/vive-flow/overview/',
    'http://vive-vn.local/shop/collections/vive-flow-for-business/',
    'http://vive-vn.local/shop/products/flow-with-controller-for-business/',
    'http://vive-vn.local/shop/products/flow-for-business/'
]

for url in urls:
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            html = resp.read().decode('utf-8')
            has_warning = 'Warning:' in html
            has_error = 'Fatal error' in html
            print(f"URL: {url} -> Status: 200 OK | Warning: {has_warning} | Fatal: {has_error}")
    except Exception as e:
        print(f"URL: {url} -> Error: {e}")
