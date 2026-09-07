import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

urls = [
    'http://vive-vn.local/shop/products/vive-eagle/',
    'http://vive-vn.local/shop/products/htc-vive-eagle-pantos-clear-lens-charcoal-black/',
    'http://vive-vn.local/shop/collections/vive-eagle-out-now/',
    'http://vive-vn.local/shop/collections/',
    'http://vive-vn.local/shop/'
]

for url in urls:
    print('========================================')
    print('URL:', url)
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req) as resp:
            content = resp.read().decode('utf-8', errors='ignore')
            
        print('Length:', len(content))
        # Find prices like $ or USD
        prices = re.findall(r'[\$]\s*\d+[\d\.,]*', content)
        print('Raw $ prices found:', len(prices), prices[:5])
        
        # Check 'Còn hàng'
        con_hang = len(re.findall(r'Còn hàng', content, re.I))
        print('Count "Còn hàng":', con_hang)
        
        # Check variants
        variants = len(re.findall(r'product-variants|Chọn các biến thể|Select Style|Select Lens|Select Color|product-style', content, re.I))
        print('Variants markers:', variants)
    except Exception as e:
        print('Error:', e)
