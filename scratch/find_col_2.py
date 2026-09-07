import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

cols = [
    'vive-flow',
    'vive-flow-for-business',
    'flow-for-business',
    'vive-xr-elite-for-business',
    'xr-series',
    'vive-pro-series-for-business',
    'pro-series',
    'vive-mars-series',
    'vive-focus-series-for-business',
    'focus-series',
    'vive-eagle-out-now',
    'vive-eagle-1',
    'accessories',
    'headsets',
    'on-sale',
    'battery_included'
]

for c in cols:
    url = f'http://vive-vn.local/shop/collections/{c}/'
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=5) as resp:
            html = resp.read().decode('utf-8')
        count_match = re.search(r'id="CollectionProductCount"[^>]*>(.*?)</span>', html)
        count_text = count_match.group(1) if count_match else ''
        if '2 sản phẩm' in count_text or '2' in count_text:
            print(f"Collection: {c} -> {count_text}")
            # Find links in product items
            links = re.findall(r'class="product-item__title"\s+href="([^"]+)"', html)
            print(" - Links:", links)
    except Exception as e:
        pass
