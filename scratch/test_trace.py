import re
from bs4 import BeautifulSoup

with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

def check(step, html_str):
    soup = BeautifulSoup(html_str, 'html.parser')
    page = soup.find('product-page')
    children = [f"<{c.name}> class={c.get('class')}" for c in page.find_all(recursive=False)]
    print(f"[{step}] children count={len(children)}")
    if len(children) > 2:
        print("   BROKEN HERE! Direct children:", children)
        return False
    return True

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'

# Step 1
html = preg_sub = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bproduct-price--original\b[^"\']*["\'][^>]*>[\s\S]*?</span>', in_stock_html, html, flags=re.IGNORECASE)
if check("Step 1 (original price)", html):
    pass

# Step 2
html = re.sub(r'<del\b[^>]*class=["\'][^"\']*\bproduct-price--compare\b[^"\']*["\'][^>]*>[\s\S]*?</del>', '', html, flags=re.IGNORECASE)
check("Step 2 (compare price)", html)

# Step 3
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
check("Step 3 (product-price div)", html)

# Step 4
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="product-item__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
check("Step 4 (item price)", html)

# Step 5
html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price-varies\b[^"\']*["\'][^>]*>[\s\S]*?</span>', in_stock_html, html, flags=re.IGNORECASE)
check("Step 5 (sticky price varies)", html)

# Step 6
html = re.sub(r'<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>[\s\S]*?</div>', '<div class="sticky-add-to-cart__price">' + in_stock_html + '</div>', html, flags=re.IGNORECASE)
check("Step 6 (sticky price div)", html)

# Step 7
html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>', '<span class="sidebar-widget__title">Tình trạng</span>', html, flags=re.IGNORECASE)
check("Step 7 (sidebar widget title)", html)

# Step 8
html = re.sub(r'>\s*(?:Đang được bán từ\s*)?\$[0-9]+[0-9\.,]*\s*(?:USD)?\s*<', '>' + in_stock_html + '<', html, flags=re.IGNORECASE)
check("Step 8 (dollar amounts)", html)
