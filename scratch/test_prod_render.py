import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/shop/products/test/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as resp:
        html = resp.read().decode('utf-8')
    print('HTTP Status: 200 OK')
    print('Has <show-more:', '<show-more' in html)
    print('Has Xem them:', 'Xem thêm' in html)
    print('Has VIVE Flow static text:', 'Với trọng lượng chỉ 189' in html)
    print('Has test product desc:', 'Đây là sản phẩm thử nghiệm' in html)
    
    match = re.search(r'<div class="product__description[\s\S]*?</div>', html)
    if match:
        print('\nDescription block:')
        print(match.group(0))
except Exception as e:
    print('Error:', e)
