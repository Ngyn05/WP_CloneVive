import re

with open('pages/us/product/index.htm', encoding='utf-8') as f:
    html = f.read()

listing_map = {
    'vive-eagle': {
        'title': 'Kính AI VIVE Eagle',
        'detail': '/shop/products/vive-eagle/',
        'buy': '/shop/products/vive-eagle/'
    },
    'vive-focus-vision': {
        'title': 'VIVE Focus Series',
        'detail': '/product/vive-focus-vision/overview/',
        'buy': '/shop/products/21071155/'
    },
    'vive-xr-elite': {
        'title': 'VIVE XR Elite',
        'detail': '/product/vive-xr-elite/overview/',
        'buy': '/shop/products/vive-xr-elite-business-edition/'
    },
    'vive-pro-2-full-kit': {
        'title': 'VIVE Pro 2 Full Kit',
        'detail': '/product/vive-pro2-full-kit/overview/',
        'buy': '/shop/collections/pro-series/'
    },
    'vive-pro-2': {
        'title': 'VIVE Pro 2 Headset',
        'detail': '/product/vive-pro2/overview/',
        'buy': '/shop/collections/pro-series/'
    },
    'vive-flow': {
        'title': 'VIVE Flow',
        'detail': '/product/vive-flow/overview/',
        'buy': '/shop/products/21071655/'
    }
}

# Replace item blocks on listing page
def replace_item_block(match):
    slug = match.group('slug').lower()
    info = listing_map.get(slug, {
        'detail': f'/shop/products/{slug}/',
        'buy': f'/shop/products/{slug}/'
    })
    
    return f'''<div class="theme-vive-product-actions" style="display:flex!important;gap:12px;margin-top:16px;">
        <a class="theme-vive-detail-button" href="{info['detail']}">Xem chi tiết</a>
        <a class="theme-vive-buy-button" href="{info['buy']}">Mua ngay</a>
    </div>'''

pattern = r'<div\b[^>]*class=["\'][^"\']*\bitem-block\b[^"\']*["\'][^>]*data-slug=["\'](?P<slug>[^"\']+)["\'][^>]*>.*?</div>\s*(?:</div>)?'
new_html = re.sub(pattern, replace_item_block, html, flags=re.DOTALL | re.IGNORECASE)

print("Replaced listing item blocks count:", len(re.findall(pattern, html, flags=re.DOTALL | re.IGNORECASE)))
