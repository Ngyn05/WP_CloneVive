with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re

m = re.search(r'(<div class="fields-search-modal">[\s\S]*?</div>\s*</div>\s*)(?=</div>\s*</div>\s*</div>\s*</section>)', html)
if m:
    print("Found fields-search-modal block. Length:", len(m.group(1)))
    print("Start:", m.group(1)[:200])
    print("End:", m.group(1)[-200:])
else:
    print("Not found by exact regex, searching wider...")
    m2 = re.search(r'<div class="fields-search-modal">[\s\S]*?</section>', html)
    if m2:
        print("Wider snippet:\n", m2.group(0))
