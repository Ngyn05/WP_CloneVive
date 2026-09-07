import urllib.request

url = 'http://vive-vn.local/shop/products/vive-eagle/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

with open('scratch/live.html', 'w', encoding='utf-8') as f:
    f.write(html)
print("Saved scratch/live.html (length: %d)" % len(html))
