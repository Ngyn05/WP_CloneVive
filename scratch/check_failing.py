with open('shop-us/products/vive-focus-vision-pc-vr-wired-bundle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

idx = html.index('class="product-actions"')
end_idx = html.index('</product-page>')
txt = html[idx:end_idx+20].encode('ascii', 'backslashreplace').decode('ascii')
print(txt)
