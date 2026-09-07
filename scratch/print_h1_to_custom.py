with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(2395, 2435):
    txt = lines[i].encode('ascii', 'backslashreplace').decode('ascii')
    print(f"{i+1}: {txt}", end="")
