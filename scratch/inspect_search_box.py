import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

m = re.search(r'(<div class="fields-search-box">[\s\S]*?</div>\s*</div>)', html)
if m:
    print("Found fields-search-box:")
    print(m.group(0))
