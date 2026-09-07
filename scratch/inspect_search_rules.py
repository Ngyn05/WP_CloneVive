import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/assets/media/filer_public/fed-assets/vive-flow-2022/css/default.css', 'r', encoding='utf-8') as f:
    css = f.read()

for m in re.finditer(r'[^}]*section-search-compatibility[^{]*wording-area[^{]*\{[^}]*\}', css):
    print("RULE:", m.group(0))

for m in re.finditer(r'[^}]*section-search-compatibility[^{]*\{[^}]*\}', css):
    if 'wording' not in m.group(0) and 'fields-modal' not in m.group(0):
        print("BASE RULE:", m.group(0))
