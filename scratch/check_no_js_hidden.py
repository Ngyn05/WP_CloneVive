import re
import urllib.request
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = "https://shop-us.vive.com/cdn/shop/t/25/assets/theme.css?v=105739051976567433851783018041"
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
with urllib.request.urlopen(req) as resp:
    css = resp.read().decode('utf-8', errors='ignore')

matches = list(re.finditer(r'[^{};]*no-js-hidden[^{};]*\{[^}]*\}', css))
print(f"Found {len(matches)} rules for no-js-hidden in theme.css:")
for m in matches:
    print(m.group(0))

