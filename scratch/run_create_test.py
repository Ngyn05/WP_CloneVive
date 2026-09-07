import urllib.request

url = 'http://vive-vn.local/wp-content/themes/theme_vive/scratch/create_test_product.php'
try:
    resp = urllib.request.urlopen(url)
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/scratch/err.html', 'w', encoding='utf-8') as f:
        f.write(resp.read().decode('utf-8'))
except urllib.error.HTTPError as e:
    with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/scratch/err.html', 'w', encoding='utf-8') as f:
        f.write(e.read().decode('utf-8', errors='ignore'))
