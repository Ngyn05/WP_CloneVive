with open('shop-us/products/vive-eagle/index.htm', 'r', encoding='utf-8') as f:
    html = f.read()

from bs4 import BeautifulSoup
soup = BeautifulSoup(html, 'html.parser')
widget = soup.find(class_='sidebar-widget--outline')
if widget:
    print("Widget HTML snippet:")
    print(str(widget)[:1000])
else:
    print("Widget NOT found in raw html")
