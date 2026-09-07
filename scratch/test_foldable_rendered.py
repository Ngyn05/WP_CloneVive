import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/product/vive-flow/overview/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=5) as resp:
        html = resp.read().decode('utf-8')
    match = re.search(r'<div class="foldable-left-text"[\s\S]*?</div>\s*<div class="foldable-left-weight"[\s\S]*?</div>', html)
    if match:
        print("Foldable snippet:")
        print(match.group(0))
    else:
        print("Not found snippet")
except Exception as e:
    print('Error:', e)
