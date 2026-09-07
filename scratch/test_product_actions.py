import glob
import re

files = ['shop-us/products/vive-eagle/index.htm', 'shop-us/products/vive-xr-elite/index.htm', 'shop-us/products/vive-focus-vision/index.htm']
for f in files:
    try:
        with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
            html = fp.read()
        
        pos = html.find('class="product-actions')
        if pos != -1:
            print(f"=== File: {f} ===")
            print(html[pos:pos+1500])
    except Exception as e:
        print(f"Error {f}: {e}")
