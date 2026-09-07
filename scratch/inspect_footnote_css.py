import re

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/assets/media/filer_public/fed-assets/vive-flow-2022/css/default.css', 'r', encoding='utf-8') as f:
    css = f.read()

for m in re.finditer(r'[^}]*section-footnote[^}]*\}', css):
    print("CSS:", m.group(0))
