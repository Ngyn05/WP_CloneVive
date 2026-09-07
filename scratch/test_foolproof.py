import glob
import os
import re
from bs4 import BeautifulSoup

in_stock_html = '<span class="vive-in-stock-badge">Còn hàng</span>'
actions_html = '<div class="product-quantity">QTY</div><div class="vive-woo-product__actions"><a class="vive-woo-product__action--buy">MUA NGAY</a></div><form class="vive-woo-product__callback">CALLBACK</form>'

product_files = glob.glob('shop-us/products/*/index.htm')
print(f"Testing on {len(product_files)} products...")

broken_count = 0
for pf in product_files:
    with open(pf, 'r', encoding='utf-8') as f:
        html = f.read()

    # 1. Remove installment forms and shopify-payment-terms (no nested div regex!)
    html = re.sub(r'<form\b[^>]*class=["\'][^"\']*product-form-installment[^"\']*["\'][^>]*>[\s\S]*?</form>', '', html, flags=re.I)
    html = re.sub(r'<shopify-payment-terms\b[^>]*>[\s\S]*?</shopify-payment-terms>', '', html, flags=re.I)

    # 2. Replace only innermost price elements or price text
    # Replace the text inside .product-price with in_stock_html
    html = re.sub(r'(<div\b[^>]*class=["\'][^"\']*\bproduct-price\b[^"\']*["\'][^>]*>)[\s\S]*?(</div>)', r'\1' + in_stock_html + r'\2', html, flags=re.I)
    html = re.sub(r'(<div\b[^>]*class=["\'][^"\']*\bproduct-item__price\b[^"\']*["\'][^>]*>)[\s\S]*?(</div>)', r'\1' + in_stock_html + r'\2', html, flags=re.I)
    html = re.sub(r'(<div\b[^>]*class=["\'][^"\']*\bsticky-add-to-cart__price\b[^"\']*["\'][^>]*>)[\s\S]*?(</div>)', r'\1' + in_stock_html + r'\2', html, flags=re.I)
    html = re.sub(r'<span\b[^>]*class=["\'][^"\']*\bsidebar-widget__title\b[^"\']*["\'][^>]*>\s*(?:Giá bán|Price)\s*</span>', '<span class="sidebar-widget__title">Tình trạng</span>', html, flags=re.I)
    html = re.sub(r'>\s*(?:Đang được bán từ\s*)?\$[0-9]+[0-9\.,]*\s*(?:USD)?\s*<', '>' + in_stock_html + '<', html, flags=re.I)

    # 3. Replace <product-form>...</product-form> with actions_html
    if re.search(r'<product-form\b', html, re.I):
        html = re.sub(r'<product-form\b[^>]*>[\s\S]*?</product-form>', actions_html, html, count=1, flags=re.I)
    elif re.search(r'<form\b[^>]*action=["\'][^"\']*/cart/add', html, re.I):
        html = re.sub(r'<form\b[^>]*action=["\'][^"\']*/cart/add[^"\']*["\'][^>]*>[\s\S]*?</form>', actions_html, html, count=1, flags=re.I)

    soup = BeautifulSoup(html, 'html.parser')
    page = soup.find('product-page')
    if page:
        children = page.find_all(recursive=False)
        if len(children) != 2:
            print(f"[FAIL] {os.path.basename(os.path.dirname(pf))} has {len(children)} direct children!")
            broken_count += 1

print(f"RESULTS: {len(product_files) - broken_count} / {len(product_files)} passed! (Failures: {broken_count})")
