import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos_act = text.find('product-actions')
print("=== HTML around product-actions in raw file ===")
print(text[pos_act-100:pos_act+2500])
