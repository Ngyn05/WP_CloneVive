with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

import re

pattern = r'<div class="fields-search-modal">[\s\S]*?</div>\s*</div>\s*(?=</div>\s*</div>\s*</div>\s*</section>)'
html = re.sub(pattern, '', html, count=1)

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/pages/us/product/vive-flow/overview/index.htm', 'w', encoding='utf-8') as f:
    f.write(html)

print("Successfully removed fields-search-modal from HTML!")
