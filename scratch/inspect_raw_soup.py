with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    raw = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(raw, 'html.parser')
page = soup.find('product-page')
print("RAW HTML product-page children:", [c.name for c in page.find_all(recursive=False)])

p_text = soup.find(class_='product-text')
print("RAW HTML p_text direct children:", [f"<{c.name}> class={c.get('class')}" for c in p_text.find_all(recursive=False)])
print("RAW HTML variants in p_text?", p_text.find('product-variants') is not None)
print("RAW HTML product-form in p_text?", p_text.find('product-form') is not None)
