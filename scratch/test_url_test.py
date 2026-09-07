import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

def test_url(url):
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        with urllib.request.urlopen(req, timeout=5) as resp:
            html = resp.read().decode('utf-8')
            titles = re.findall(r'<h1[^>]*>([\s\S]*?)</h1>', html)
            clean_titles = [re.sub(r'\s+', ' ', t).strip() for t in titles]
            print(f"URL: {url} -> Status: 200 | Titles ({len(titles)}): {clean_titles}")
    except Exception as e:
        print(f"URL: {url} -> Error: {e}")

test_url('http://vive-vn.local/shop/products/test/')
