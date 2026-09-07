with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    raw = f.read()

with open('scratch/test_out.html', 'r', encoding='utf-8') as f:
    out = f.read()

import difflib
diff = list(difflib.unified_diff(raw.splitlines()[:2850], out.splitlines()[:2850], lineterm=''))
for d in diff[:100]:
    print(d.encode('ascii', 'backslashreplace').decode('ascii'))
