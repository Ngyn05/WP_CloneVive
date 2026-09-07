import urllib.request
import sys

url = 'http://vive-vn.local/wp-content/themes/theme_vive/scratch/list_recent_prods.php'
try:
    resp = urllib.request.urlopen(url)
    content = resp.read().decode('utf-8')
    sys.stdout.buffer.write(content.encode('utf-8'))
except Exception as e:
    print("Error:", e)
