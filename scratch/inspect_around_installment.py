import sys
sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

pos = html.find('product-form-installment')
print(html[pos:pos+2000])
