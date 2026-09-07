with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

print("--- Context around Match 1 (173141) ---")
print(html[172500:173300])

print("\n--- Context around Match 2 (261202) ---")
print(html[260500:261400])
