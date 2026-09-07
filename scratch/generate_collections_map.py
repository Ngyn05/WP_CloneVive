import json
import glob
import os
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

with open('shop-us/all_products.json', 'r', encoding='utf-8') as f:
    all_products = json.load(f)

# Collection titles mapping in Vietnamese
collection_meta = {
    'vive-xr-elite-for-business': {
        'name': 'VIVE XR Elite cho Doanh nghiệp',
        'desc': 'Giải pháp thực tế mở rộng XR toàn diện cho đào tạo, mô phỏng và cộng tác chuyên nghiệp.',
        'order': 10
    },
    'xr-series': {
        'name': 'Kính thực tế ảo VIVE XR Elite',
        'desc': 'Dòng kính XR tất cả trong một có thể chuyển đổi, thiết kế gọn nhẹ và hiệu năng vượt trội.',
        'order': 20
    },
    'vive-ultimate-tracker': {
        'name': 'Thiết bị theo dõi VIVE Ultimate Tracker',
        'desc': 'Cảm biến theo dõi chuyển động độc lập chính xác cao không cần trạm thu phát cơ sở.',
        'order': 30
    },
    'vive-pro-series-for-business': {
        'name': 'Dòng VIVE Pro cho Doanh nghiệp',
        'desc': 'Hệ thống VR chuyên nghiệp độ chính xác cao cho kiến trúc, y tế và đào tạo giả lập.',
        'order': 40
    },
    'pro-series': {
        'name': 'Dòng kính VIVE Pro',
        'desc': 'Dòng kính VR cao cấp dành cho game thủ chuyên nghiệp và nhà phát triển nội dung.',
        'order': 50
    },
    'vive-mars-series': {
        'name': 'Hệ thống trường quay ảo VIVE Mars',
        'desc': 'Giải pháp theo dõi máy quay ảo CamTrack chuyên nghiệp dành cho sản xuất phim và phát sóng.',
        'order': 60
    },
    'vive-focus-series-for-business': {
        'name': 'Dòng VIVE Focus cho Doanh nghiệp',
        'desc': 'Dòng kính VR All-in-one cao cấp tối ưu hóa cho môi trường doanh nghiệp và tổ chức.',
        'order': 70
    },
    'focus-series': {
        'name': 'Dòng kính VIVE Focus',
        'desc': 'Dòng kính VR độc lập tất cả trong một độ phân giải cao và tính linh hoạt hàng đầu.',
        'order': 80
    },
    'vive-flow-for-business': {
        'name': 'VIVE Flow cho Doanh nghiệp',
        'desc': 'Kính VR siêu gọn nhẹ phục vụ chăm sóc sức khỏe tinh thần, đào tạo và giải trí doanh nghiệp.',
        'order': 90
    },
    'vive-flow': {
        'name': 'Kính thực tế ảo VIVE Flow',
        'desc': 'Kính VR thư giãn và giải trí di động nhỏ gọn, dễ dàng mang theo mọi lúc mọi nơi.',
        'order': 100
    },
    'vive-eagle-out-now': {
        'name': 'Kính thông minh AI VIVE Eagle',
        'desc': 'Kính AI thời trang đột phá tích hợp trợ lý AI thông minh, camera chụp ảnh và âm thanh không gian.',
        'order': 110
    },
    'vive-eagle-1': {
        'name': 'Dòng kính AI VIVE Eagle',
        'desc': 'Bộ sưu tập kính AI thông minh VIVE Eagle với nhiều kiểu gọng gập và tròng kính thời trang.',
        'order': 120
    },
    'accessories': {
        'name': 'Phụ kiện VIVE',
        'desc': 'Bộ phụ kiện chính hãng gồm cáp kết nối, đệm mặt, pin dự phòng, bộ sạc và tay cầm điều khiển.',
        'order': 130
    },
    'headsets': {
        'name': 'Kính thực tế ảo (VR Headsets)',
        'desc': 'Tất cả các dòng kính thực tế ảo và thực tế tăng cường VR/XR/AR chính hãng HTC VIVE.',
        'order': 140
    },
    'on-sale': {
        'name': 'Ưu đãi & Khuyến mãi',
        'desc': 'Tổng hợp các chương trình ưu đãi, giảm giá và quà tặng hấp dẫn cho thiết bị VIVE.',
        'order': 150
    },
    'battery_included': {
        'name': 'Bộ nguồn & Pin sạc',
        'desc': 'Các giải pháp cấp nguồn, dock sạc nhanh, pin thay thế và adapter cấp nguồn liên tục.',
        'order': 160
    }
}

# Parse collection HTMLs
collections_products = {}
for cf in glob.glob('shop-us/collections/*/index.htm'):
    handle = os.path.basename(os.path.dirname(cf))
    if handle in ['index.htm', 'collections_list.json', 'all']:
        continue
    with open(cf, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()
    products = set(re.findall(r'/products/([a-zA-Z0-9_-]+)', html))
    collections_products[handle] = sorted(list(products))

# Specific Eagle fix: ensure all eagle variations and eagle main are in Eagle collections
for p in all_products:
    h = p['handle']
    if 'eagle' in h:
        if 'vive-eagle-out-now' in collections_products:
            if h not in collections_products['vive-eagle-out-now']:
                collections_products['vive-eagle-out-now'].append(h)
        if 'vive-eagle-1' in collections_products:
            if h not in collections_products['vive-eagle-1']:
                collections_products['vive-eagle-1'].append(h)

# Map each product to list of categories
product_cats_map = {}
for p in all_products:
    h = p['handle']
    cats = []
    for c_handle, prods in collections_products.items():
        if h in prods:
            c_name = collection_meta.get(c_handle, {}).get('name', c_handle)
            if c_name not in cats:
                cats.append(c_name)
    
    # If no category, infer from product handle / product_type
    if not cats:
        p_type = p.get('product_type') or 'Phụ kiện VIVE'
        if 'focus' in h:
            cats.append('Dòng kính VIVE Focus')
        elif 'pro' in h:
            cats.append('Dòng kính VIVE Pro')
        elif 'xr' in h or 'elite' in h:
            cats.append('Kính thực tế ảo VIVE XR Elite')
        elif 'flow' in h:
            cats.append('Kính thực tế ảo VIVE Flow')
        elif 'tracker' in h:
            cats.append('Thiết bị theo dõi VIVE Ultimate Tracker')
            cats.append('Phụ kiện VIVE')
        elif 'mars' in h:
            cats.append('Hệ thống trường quay ảo VIVE Mars')
        elif 'eagle' in h:
            cats.append('Kính thông minh AI VIVE Eagle')
        else:
            cats.append('Phụ kiện VIVE')
            
    product_cats_map[h] = cats

export_data = {
    'categories': collection_meta,
    'collections_products': collections_products,
    'product_to_categories': product_cats_map
}

with open('shop-us/collections_and_products.json', 'w', encoding='utf-8') as f:
    json.dump(export_data, f, ensure_ascii=False, indent=2)

print("Generated shop-us/collections_and_products.json successfully!")
print(f"Total categories: {len(collection_meta)}")
print(f"Total products mapped: {len(product_cats_map)}")
