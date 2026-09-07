import urllib.request
import re

url = 'http://vive-vn.local/shop/collections/vive-flow/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
html = urllib.request.urlopen(req).read().decode('utf-8')

print("Page HTML size:", len(html))
# Let's inspect what is inside #main-collection-product-grid
grid_match = re.search(r'(<div\b[^>]*\bid=["\']main-collection-product-grid["\'][^>]*>[\s\S]*?</div>\s*</div>)', html)
if grid_match:
    content = grid_match.group(0)
    with open("c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/scratch/grid_output.html", "w", encoding="utf-8") as f:
        f.write(content)
    print("Saved grid output, length:", len(content))
else:
    print("Grid not matched!")
