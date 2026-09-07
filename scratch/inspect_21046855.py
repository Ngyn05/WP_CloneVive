import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

url = 'http://vive-vn.local/shop/products/21046855/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
try:
    with urllib.request.urlopen(req) as resp:
        html = resp.read().decode('utf-8')
    print('HTTP Status: 200 OK')
    print('Length:', len(html))
    
    # Check if there are multiple titles or duplicate sections
    titles = re.findall(r'<h1[^>]*>[\s\S]*?</h1>', html)
    print(f"Total <h1>: {len(titles)}")
    for t in titles:
        print(" -", t.strip())
        
    # Check for duplicate product forms or galleries
    galleries = re.findall(r'class="[^"]*product-gallery[^"]*"', html)
    print(f"Total product-gallery: {len(galleries)}")
    
    # Let's see what shopify-sections exist
    sec = re.findall(r'id="(shopify-section-[^"]+)"', html)
    print("Sections:", sec)
except Exception as e:
    print('Error:', e)
