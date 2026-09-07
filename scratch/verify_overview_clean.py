import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/product/vive-flow/overview/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=5) as resp:
        html = resp.read().decode('utf-8')
    has_search_box = 'fields-search-box' in html
    print("Status: 200 OK")
    print("Has fields-search-box:", has_search_box)
    
    match = re.search(r'<div class="wording-area">[\s\S]*?</div>\s*</div>', html)
    if match:
        print("\nRendered wording area snippet:\n", match.group(0))
except Exception as e:
    print('Error:', e)
