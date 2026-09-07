import urllib.request

# Let's create a test product in WooCommerce if it doesn't exist, or test what response we get
req = urllib.request.Request('http://vive-vn.local/shop/products/test/', headers={'User-Agent': 'Mozilla/5.0'})
try:
    resp = urllib.request.urlopen(req)
    print("Response code:", resp.getcode())
except urllib.error.HTTPError as e:
    print("HTTP Error:", e.code)
