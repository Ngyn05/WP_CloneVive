import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/shop/collections/vive-flow-for-business/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8')

# Find product item container markup
match = re.search(r'<div class="product-item"[\s\S]*?</div>\s*</div>\s*</div>', html)
if match:
    print("Product item HTML snippet:")
    print(match.group(0))
