import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

# Search for product-actions grid rules
for file in ['style.css', 'inc/product-cms.php']:
    with open(file, 'r', encoding='utf-8', errors='ignore') as f:
        c = f.read()
    print(f"File {file}:")
    for m in re.finditer(r'[^{};]*product-actions[^{};]*\{[^}]*\}', c):
        print("  ", m.group(0).strip())

