import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/product/vive-flow/overview/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req, timeout=5) as resp:
        html = resp.read().decode('utf-8')
    print("Status: 200 OK")
    print("Has fields-search-modal in HTML:", 'fields-search-modal' in html)
    print("Has old modal text (Chưa được xác minh):", 'chưa được xác minh' in html.lower())
    
    match = re.search(r'<section class="section-footnote"[\s\S]*?</section>', html)
    if match:
        print("\nFootnote snippet:\n", match.group(0)[:300])
except Exception as e:
    print('Error:', e)
