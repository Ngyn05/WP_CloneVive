import urllib.request

url = 'http://vive-vn.local/shop/products/test/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    resp = urllib.request.urlopen(req)
    content = resp.read().decode('utf-8')
    print("STATUS CODE:", resp.getcode())
    print("Page length:", len(content))
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/scratch/test_product_page_output.html', 'w', encoding='utf-8') as f:
        f.write(content)
except Exception as e:
    print("ERROR:", e)
