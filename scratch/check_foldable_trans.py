import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/inc/translations-auto-vi.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

for k, v in data.items():
    if 'foldable' in k.lower() or '189' in k:
        print(f"[{k}] => [{v}]")
