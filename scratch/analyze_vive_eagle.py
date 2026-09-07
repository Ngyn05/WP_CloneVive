import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_text = text.find('class="product-text')
print(text[pos_text+7000:pos_text+13000])
