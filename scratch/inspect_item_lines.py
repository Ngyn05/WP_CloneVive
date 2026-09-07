with open('shop-us/products/2104005/index.htm', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(1540, min(len(lines), 1600)):
    txt = lines[i].encode('ascii', 'backslashreplace').decode('ascii')
    print(f"{i+1}: {txt}", end="")
