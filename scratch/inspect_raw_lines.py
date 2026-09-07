with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(2800, 2830):
    if i < len(lines):
        txt = lines[i].encode('ascii', 'backslashreplace').decode('ascii')
        print(f"{i+1}: {txt}", end="")
