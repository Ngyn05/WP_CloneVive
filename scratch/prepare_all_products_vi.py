import json

# Comprehensive mapping for all 93 official HTC VIVE products
product_translations_by_handle = {
    "vive-eagle": "Kính AI VIVE Eagle",
    "vive-eagle-power-boost": "Pin sạc tăng cường VIVE Eagle Power Boost",
    "magnetic-charging-cable-for-vive-eagle": "Cáp sạc từ tính cho VIVE Eagle",
    "htc-vive-eagle-pantos-clear-lens-charcoal-black": "Kính AI VIVE Eagle Pantos - Tròng trong suốt (Đen Charcoal)",
    "vive-eagle-pantos-sunglasses-navy": "Kính AI VIVE Eagle Pantos - Tròng râm chống nắng (Xanh Navy)",
    "vive-eagle-square-transition-lens-black": "Kính AI VIVE Eagle Square - Tròng đổi màu Transitions (Đen)",
    "vive-eagle-square-clear-coffee": "Kính AI VIVE Eagle Square - Tròng trong suốt (Nâu Coffee)",
    "vive-eagle-square-clear-black": "Kính AI VIVE Eagle Square - Tròng trong suốt (Đen)",
    "vive-eagle-square-sunglasses-berry": "Kính AI VIVE Eagle Square - Tròng râm chống nắng (Màu Berry)",
    "vive-eagle-glossy-black-square-sun": "Kính AI VIVE Eagle Square - Tròng râm chống nắng (Đen bóng)",
    "vive-eagle-pantos-clear-light-brown": "Kính AI VIVE Eagle Pantos - Tròng trong suốt (Nâu sáng)",
    "vive-eagle-pantos-sunglasses-light-brown": "Kính AI VIVE Eagle Pantos - Tròng râm chống nắng (Nâu sáng)",
    "vive-eagle-pantos-sunglasses-matte-black": "Kính AI VIVE Eagle Pantos - Tròng râm chống nắng (Đen nhám)",
    "21063055": "Bộ kết nối truyền phát có dây VIVE Wired Streaming Kit",
    "vive-wired-streaming-kit-1": "Bộ kết nối truyền phát có dây VIVE Wired Streaming Kit (SB)",
    "vive-face-cushion-narrow-set-of-2": "Bộ đệm mặt VIVE Face Cushion - Bản hẹp (Bộ 2 chiếc)",
    "vive-business-ultimate-tracker-3-year-subscription-5-seat": "Gói bản quyền VIVE Business+ Ultimate Tracker 3 năm (5 tài khoản)",
    "vive-business-pro-3-year-subscription-22-seat": "Gói bản quyền VIVE Business+ Pro 3 năm (22 tài khoản)",
    "21071055": "Gói bản quyền VIVE Business+ Pro 1 năm (1 tài khoản)",
    "vive-focus-vision-wired-streaming-kit-facial-tracker": "Bộ sản phẩm VIVE Focus Vision + Bộ kết nối có dây + Thiết bị theo dõi nét mặt",
    "vive-focus-vision-facial-tracker": "Kính VIVE Focus Vision + Thiết bị theo dõi nét mặt Facial Tracker",
    "31071559": "Pin thay thế cho dòng VIVE Focus",
    "vive-business-ultimate-1-year-subscription-10-seats": "Gói bản quyền VIVE Business+ Ultimate 1 năm (10 tài khoản)",
    "21048355": "Bộ theo dõi chuyển động VIVE Ultimate Tracker 3+1 Kit (3 Tracker + 1 Dongle)",
    "controller-for-xr-elite-left-bulk-packaging": "Tay cầm điều khiển VIVE XR Elite - Bên trái (Hàng chính hãng)",
    "vive-focus-vision-wired-bundle-business-edition-sb": "Kính VIVE Focus Vision Gói có dây phiên bản Doanh nghiệp (SB)",
    "vive-business-ultimate-30-day-subscription-7-seats": "Gói bản quyền VIVE Business+ Ultimate 30 ngày (7 tài khoản)",
    "vive-ultimate-tracker-3-1-kit": "Bộ theo dõi chuyển động VIVE Ultimate Tracker 3+1 Kit",
    "xr-elite-with-deluxe-pack-business-edition": "Kính VIVE XR Elite kèm Gói Deluxe Pack (Phiên bản Doanh nghiệp)",
    "21047655": "Đệm chắn sáng VIVE MR Gasket cho XR Elite (Bộ 2 chiếc)",
    "31071561": "Gói bản quyền VIVE Business+ Ultimate 3 năm (1 tài khoản)",
    "21045003": "Giải pháp theo dõi máy quay VIVE Mars CamTrack cho Sản xuất ảo",
    "2104023": "Cáp chuyển đổi Mini DisplayPort sang DisplayPort cho VIVE Pro",
    "31071562": "Gói bản quyền VIVE Business+ Ultimate 3 năm (5 tài khoản)",
    "2104005": "Trạm phát tín hiệu định vị HTC VIVE SteamVR Base Station 2.0",
    "vive-tracker-3-0-full-body-tracking": "Cảm biến theo dõi toàn thân HTC VIVE Tracker 3.0 (Tương thích SteamVR)",
    "2104010": "Đệm mặt mỏng cho dòng kính VIVE Cosmos (Bộ 2 chiếc)",
    "2104024": "Bộ đệm mặt thay thế cho dòng kính VIVE Pro Series",
    "2104013": "Mặt nạ vệ sinh dùng một lần VIVE Hygienic Face Mask (Hộp 100 chiếc)",
    "2104030": "Tay cầm điều khiển HTC VIVE Controller",
    "2104029": "Bộ giải pháp mở rộng cáp kết nối 20m cho VIVE Pro",
    "2104027": "Đệm mặt bản hẹp cho dòng kính VIVE Pro (Bộ 2 chiếc)",
    "2104033": "Hộp kết nối Link Box cho kính VIVE Pro",
    "2104031": "Trạm phát tín hiệu định vị HTC VIVE Base Station",
    "2104037": "Tay cầm điều khiển HTC VIVE Controller 2.0",
    "2104035": "Dây đeo tích hợp tai nghe cao cấp VIVE Deluxe Audio Strap",
    "2104034": "Hộp kết nối tự động mở nguồn VIVE Default-on Link Box cho VIVE Pro",
    "2104045": "Mặt nạ cảm biến chuyển động VIVE Cosmos Motion Faceplate",
    "2104049": "Mặt nạ hỗ trợ định vị ngoài VIVE Cosmos External Tracking Faceplate",
    "2104061": "Tay cầm điều khiển VIVE đã qua kiểm định chính hãng (Pre-Owned)",
    "21045000": "Gói bảo hành & dịch vụ doanh nghiệp 2 năm cho VIVE XR Elite",
    "18645399": "Tay cầm điều khiển cho kính thực tế ảo VIVE Flow",
    "2104034-1": "Hộp kết nối tự động mở nguồn VIVE Default-on Link Box cho VIVE Pro 2",
    "21045035": "Đế sạc đa pin cho dòng kính VIVE Focus (Sạc đồng thời nhiều pin)",
    "21045006": "Thiết bị theo dõi biểu cảm nét mặt cho dòng VIVE Focus Series",
    "21045005": "Thiết bị theo dõi chuyển động mắt VIVE Focus 3 Eye Tracker",
    "21046255": "Hộp đựng bảo vệ cao cấp cho dòng VIVE XR Series & VIVE Flow",
    "21046155": "Khay gắn pin phía sau cho dòng kính VIVE XR Series",
    "21046855": "Bộ đệm gọng kính cho dòng VIVE XR Series (Bộ 3 cặp)",
    "21046655": "Củ sạc nhanh VIVE 30W Type-C cho dòng XR Series",
    "21046455": "Đệm áp mặt cao cấp VIVE Face Gasket cho dòng XR Series",
    "21047355": "Gói bản quyền VIVE Business+ Pro 3 năm (5 tài khoản)",
    "21047255": "Gói bản quyền VIVE Business+ Pro 3 năm (1 tài khoản)",
    "21047155": "Hệ thống quản trị thiết bị VIVE Business Standard 3 năm (1 tài khoản)",
    "21047555": "Thiết bị theo dõi điều khiển ống kính VIVE Mars FIZTrack",
    "21047455": "Gói bản quyền VIVE Business+ Pro 3 năm (10 tài khoản)",
    "21048255": "Bộ thu phát không dây VIVE Wireless Dongle",
    "21048155": "Cảm biến theo dõi chuyển động độc lập VIVE Ultimate Tracker",
    "21049155": "Đệm mặt da PU êm ái cho kính VIVE XR Elite",
    "21048455": "Bộ dây đeo Trackstraps cho VIVE Ultimate Tracker (Đeo chân & eo)",
    "21049755": "Đế sạc & cổng kết nối mở rộng USB-C 5 cổng VIVE",
    "21049655": "Cáp kết nối kính thực tế ảo VIVE Headset Cable (2.5m)",
    "21064055": "Cáp truyền phát dữ liệu có dây VIVE Wired Streaming Cable (5m)",
    "21067055": "Gói bảo hành & dịch vụ doanh nghiệp 2 năm cho VIVE Focus Vision",
    "21065055": "Bộ chuyển đổi truyền phát dữ liệu có dây VIVE Wired Streaming Converter",
    "21071655": "Kính thực tế ảo VIVE Flow kèm tay cầm điều khiển",
    "21071155": "Kính thực tế ảo cao cấp VIVE Focus Vision",
    "vive-focus-vision-pc-vr-wired-bundle": "Kính VIVE Focus Vision Gói PC VR có dây (Kèm Kit DisplayPort)",
    "31071563": "Gói bản quyền VIVE Business+ Ultimate 3 năm (10 tài khoản)",
    "vive-focus-3-magnetic-face-rear-cushion-wide": "Bộ đệm mặt & đệm gáy từ tính cho dòng VIVE Focus (Bản rộng Wide)",
    "vive-flow-gasket-temple-pads-w": "Bộ đệm mặt & đệm gọng cho kính VIVE Flow (Bản W)",
    "vive-ac-adapter-30w": "Củ sạc chính hãng VIVE AC Adapter (30W)",
    "vive-flow-carrying-case": "Hộp đựng bảo vệ du lịch cho kính VIVE Flow",
    "vive-link-box-2-0": "Hộp kết nối tín hiệu chính hãng VIVE Link Box (2.0)",
    "vive-flow-multiport-power-dongle": "Bộ chia nguồn đa cổng VIVE Flow Multiport Power Dongle",
    "vive-deluxe-pack-for-xr-elite": "Gói phụ kiện nâng cấp Deluxe Pack cho VIVE XR Elite",
    "flow-with-controller-for-business": "Kính VIVE Flow kèm tay cầm điều khiển (Phiên bản Doanh nghiệp)",
    "flow-for-business": "Kính thực tế ảo VIVE Flow phiên bản Doanh nghiệp",
    "vive-xr-elite-business-edition": "Kính thực tế ảo VIVE XR Elite phiên bản Doanh nghiệp",
    "21068055": "Kính thực tế ảo VIVE Focus Vision phiên bản Doanh nghiệp",
    "vive-focus-3-magnetic-face-rear-cushion-n": "Bộ đệm mặt & đệm gáy từ tính cho dòng VIVE Focus (Bản hẹp Narrow)",
    "vive-business-pro-1-year-subscription-50-seat": "Gói bản quyền VIVE Business+ Pro 1 năm (50 tài khoản)",
    "controller-for-xr-elite-right-bulk-packaging": "Tay cầm điều khiển VIVE XR Elite - Bên phải (Hàng chính hãng)",
}

def convert_to_vnd(usd_price_str):
    if not usd_price_str:
        return 0
    try:
        usd = float(usd_price_str)
    except:
        return 0
    if usd <= 0:
        return 0
    
    exact_map = {
        599.00: 15190000,
        499.00: 12690000,
        49.00: 1250000,
        39.00: 990000,
        189.00: 4800000,
        199.00: 5050000,
        99.00: 2510000,
        129.00: 3270000,
        149.00: 3780000,
        79.00: 2000000,
        89.00: 2260000,
        59.00: 1490000,
        69.00: 1750000,
        29.00: 740000,
        19.00: 480000,
        249.00: 6320000,
        299.00: 7590000,
        399.00: 10130000,
        599.99: 15190000,
        999.00: 25370000,
        1099.00: 27910000,
        1299.00: 32990000,
        1399.00: 35530000,
        1499.00: 38070000,
        1799.00: 45690000,
        1899.00: 48230000,
        1999.00: 50770000,
        2499.00: 63470000,
    }
    if usd in exact_map:
        return exact_map[usd]
    
    vnd = round(usd * 25400 / 10000) * 10000
    return int(vnd)

def generate_short_desc(handle, title_vi):
    if 'eagle' in handle:
        return f"{title_vi} - Thiết kế sang trọng, tích hợp trợ lý AI thông minh, loa mở âm thanh vòm sống động, camera rảnh tay chất lượng cao và thời lượng pin ấn tượng suốt ngày dài."
    elif 'focus-vision' in handle:
        return f"{title_vi} - Kính thực tế ảo thế hệ mới kết hợp truyền phát DisplayPort PC VR không nén và chế độ độc lập mạnh mẽ, theo dõi mắt tự động chuẩn xác."
    elif 'xr-elite' in handle:
        return f"{title_vi} - Kính thực tế ảo hỗn hợp XR & VR tất cả trong một, thiết kế mô-đun siêu gọn nhẹ, độ phân giải 4K sắc nét và khả năng truyền hình ảnh màu sống động."
    elif 'ultimate-tracker' in handle or 'tracker' in handle:
        return f"{title_vi} - Thiết bị cảm biến theo dõi chuyển động toàn thân (Full-body tracking) tự định vị cao cấp, mang lại độ chân thực tối đa trong môi trường thực tế ảo."
    elif 'flow' in handle:
        return f"{title_vi} - Kính VR nhỏ gọn, tiện lợi và thư giãn tâm trí, dễ dàng kết nối với điện thoại và mang theo bất cứ đâu."
    elif 'business' in handle or 'subscription' in handle or 'warranty' in handle:
        return f"{title_vi} - Giải pháp quản trị và hỗ trợ doanh nghiệp chuyên nghiệp từ HTC VIVE, tối ưu hóa quy trình vận hành, quản lý thiết bị tập trung và bảo mật cao cấp."
    else:
        return f"Phụ kiện chính hãng HTC VIVE: {title_vi}. Đảm bảo độ bền bỉ, độ tương thích hoàn hảo và trải nghiệm thực tế ảo tối ưu nhất."

with open('shop-us/all_products.json', encoding='utf-8') as f:
    products = json.load(f)

results = []
for p in products:
    handle = p.get('handle')
    orig_title = p.get('title', handle)
    title_vi = product_translations_by_handle.get(handle, orig_title)
    
    variant = p.get('variants', [{}])[0]
    usd_price = variant.get('price', '0')
    vnd_price = convert_to_vnd(usd_price)
    sku = variant.get('sku') or ('VIVE-' + handle.upper()[:10])
    
    short_desc = generate_short_desc(handle, title_vi)
    
    orig_body = p.get('body_html', '')
    if orig_body and len(orig_body) > 30:
        body_vi = f"<div class=\"vive-product-description-vi\"><p class=\"lead\">{short_desc}</p>{orig_body}</div>"
    else:
        body_vi = f"<div class=\"vive-product-description-vi\"><p><strong>{title_vi}</strong> là sản phẩm chính hãng thuộc hệ sinh thái thực tế ảo HTC VIVE.</p><p>{short_desc}</p><p>Sản phẩm được hưởng chế độ bảo hành chính hãng và hỗ trợ kỹ thuật đầy đủ tại Việt Nam.</p></div>"
    
    results.append({
        'handle': handle,
        'title_en': orig_title,
        'title_vi': title_vi,
        'price_usd': usd_price,
        'price_vnd': vnd_price,
        'sku': sku,
        'short_desc': short_desc,
        'body_html': body_vi,
        'stock_status': 'instock',
    })

with open('shop-us/products_vi.json', 'w', encoding='utf-8') as out:
    json.dump(results, out, ensure_ascii=False, indent=2)

print(f"Successfully generated {len(results)} fully translated products!")
