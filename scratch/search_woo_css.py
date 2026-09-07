import re

with open('style.css', 'r', encoding='utf-8', errors='ignore') as f:
    css = f.read()

matches = re.findall(r'[^{;}]*\bvive-woo-product[^{;}]*\{[^}]*\}', css)
print(f"Found {len(matches)} rules for .vive-woo-product:")
for m in matches:
    print(m)
