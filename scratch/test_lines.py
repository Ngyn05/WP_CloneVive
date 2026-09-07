with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    if 'product-text' in line:
        print(f"product-text starts at line {idx+1}")
        for j in range(idx, idx+70):
            print(f"{j+1}: {lines[j]}", end="")
        break
