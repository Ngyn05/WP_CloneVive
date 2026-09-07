import urllib.request
from bs4 import BeautifulSoup

url = 'http://vive-vn.local/shop/products/vive-eagle/'
req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
resp = urllib.request.urlopen(req)
html = resp.read().decode('utf-8')

soup = BeautifulSoup(html, 'html.parser')
widget = soup.find(class_='sidebar-widget--outline')
if widget:
    txt = widget.prettify().encode('ascii', 'backslashreplace').decode('ascii')
    print("LIVE SIDEBAR WIDGET HTML:")
    print(txt[:2500])
else:
    print("sidebar-widget--outline not found!")
