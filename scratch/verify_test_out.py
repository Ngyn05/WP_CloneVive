with open('scratch/test_out.html', 'r', encoding='utf-8') as f:
    html = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(html, 'html.parser')
page = soup.find('product-page')
print("Direct children count:", len(page.find_all(recursive=False)))
for c in page.find_all(recursive=False):
    print(f"  <{c.name}> class={c.get('class')}")

p_text = soup.find(class_='product-text')
print("\nIs p_text found?", p_text is not None)
if p_text:
    print("p_text direct children count:", len(p_text.find_all(recursive=False)))
    for c in p_text.find_all(recursive=False):
        print(f"    <{c.name}> class={c.get('class')}")
    print("\nIs product-variants inside p_text?", p_text.find('product-variants') is not None)
    print("Is custom-actions inside p_text?", p_text.find(class_='vive-woo-product__actions') is not None)
