import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('c:/Users/hnguy/Local Sites/vive-vn/app/public/wp-content/themes/theme_vive/shop-us/products/21071655/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

main_match = re.search(r'(<div[^>]*id="shopify-section-template--[^"]*__main"[^>]*>[\s\S]*?)(?=<div[^>]*id="shopify-section-template--[^"]*__product-recommendations"|<div[^>]*id="shopify-section-sections--[^"]*__footer")', html)
if main_match:
    main_content = main_match.group(1)
    
    # Check elements inside product-text
    prod_text_match = re.search(r'(<div[^>]*class="[^"]*product-text[^"]*"[^>]*>[\s\S]*?)(?=</div>\s*</div>\s*</div>\s*</div>)', main_content)
    if prod_text_match:
        print("Product text content found. Length:", len(prod_text_match.group(1)))
        # Find all tags in product-text
        print("Tags in product-text:")
        for tag in re.findall(r'<([a-zA-Z0-9-]+)[^>]*class="([^"]*)"', prod_text_match.group(1)):
            print("  ", tag)
            
    # Check if there are other sections or divs in main_content after product-text / product-grid
    print("\nAll top-level children in main section:")
    for m in re.finditer(r'<([a-zA-Z0-9-]+)[^>]*class="([^"]*)"', main_content):
        print(f"<{m.group(1)} class='{m.group(2)[:60]}'>")
