import sys
sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

pos = html.find('heading-size--page-title')
print(html[pos:pos+1500])
