import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/product/vive-flow/overview/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    html = resp.read().decode('utf-8')

m = re.search(r'(<div class="wording-area"[^>]*>[\s\S]*?Kiểm tra tính tương thích[\s\S]*?</div>\s*</div>)', html)
if m:
    print(m.group(0))
