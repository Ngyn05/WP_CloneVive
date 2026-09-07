import json
import re

with open('scratch/all_db_pages.json', 'r', encoding='utf-8') as f:
    pages = json.load(f)

# Từ điển các cụm từ chuyên môn
translations = {
    "Trang Chu": "Trang chủ HTC VIVE Việt Nam",
    "7 Miracles": "Trải nghiệm VR 7 Miracles",
    "Accessory Comparison": "So sánh phụ kiện HTC VIVE",
    "Cookie Preferences": "Cài đặt tùy chọn Cookie",
    "Vive Flow Phone List": "Danh sách điện thoại tương thích VIVE Flow",
    "VR Racket Sports Set": "Bộ phụ kiện thể thao VR Racket",
    "Warranty": "Chính sách bảo hành chính hãng HTC VIVE",
    "Support": "Trung tâm hỗ trợ khách hàng HTC VIVE",
    "Setup": "Hướng dẫn cài đặt và tải phần mềm VIVE",
    "Contact": "Liên hệ hỗ trợ HTC VIVE Việt Nam",
    "Release Notes": "Ghi chú cập nhật phần mềm & Firmware",
    "Privacy Policy": "Chính sách quyền riêng tư HTC",
    "Legal": "Điều khoản pháp lý & Thỏa thuận dịch vụ",
    "Business": "Giải pháp thực tế ảo HTC VIVE dành cho doanh nghiệp",
    "Developer": "Cổng thông tin dành cho nhà phát triển HTC VIVE",
    "Mars": "VIVE Mars CamTrack - Giải pháp sản xuất phim ảo",
    "Sync": "VIVE Sync - Nền tảng họp và làm việc cộng tác trong VR",
    "Arts": "VIVE Arts - Số hóa nghệ thuật & Văn hóa trong VR",
    "Viverse": "VIVERSE - Vũ trụ thế giới ảo Metaverse",
    "Vive Ready": "Máy tính đạt chuẩn cấu hình VIVE Ready",
    "Vive Streaming": "Phần mềm truyền phát VIVE Streaming",
    "Vive Desk": "Phần mềm không gian làm việc ảo VIVE Desk",
    "Location Based Experiences": "Giải pháp giải trí VR theo địa điểm (LBE)",
    "Comparison": "So sánh các dòng kính thực tế ảo HTC VIVE",
    "Product": "Danh mục sản phẩm kính thực tế ảo HTC VIVE",
    "Accessory": "Danh mục phụ kiện chính hãng HTC VIVE",
}

def translate_title(p):
    slug = p['slug']
    path = p['path'].strip('/')
    title = p['title']
    
    # Neu da co trong tu dien truc tiep
    if title in translations:
        return translations[title]
        
    # Xử lý các trang phụ kiện /accessory/...
    if path.startswith('accessory/'):
        acc_name = slug.replace('-', ' ').title()
        # Dịch cụ thể các loại phụ kiện
        t = acc_name
        t = re.sub(r'30w Pd Adapter Us', 'Củ sạc nhanh 30W PD chuẩn US', t, flags=re.I)
        t = re.sub(r'Base Station2', 'Trạm phát tín hiệu Base Station 2.0', t, flags=re.I)
        t = re.sub(r'Base Station', 'Trạm phát tín hiệu Base Station', t, flags=re.I)
        t = re.sub(r'Battery For Vive Focus Series', 'Pin dự phòng cho dòng VIVE Focus', t, flags=re.I)
        t = re.sub(r'Charging Case For Vive Focus Series', 'Hộp sạc chuyên dụng dòng VIVE Focus', t, flags=re.I)
        t = re.sub(r'Clean Box', 'Hộp khử khuẩn UVC Clean Box', t, flags=re.I)
        t = re.sub(r'Controller Certified', 'Tay cầm điều khiển chứng nhận VIVE', t, flags=re.I)
        t = re.sub(r'Controller For Vive Flow', 'Tay cầm điều khiển cho VIVE Flow', t, flags=re.I)
        t = re.sub(r'Controller2018', 'Tay cầm điều khiển VIVE Pro (2018)', t, flags=re.I)
        t = re.sub(r'Controller', 'Tay cầm điều khiển VIVE', t, flags=re.I)
        t = re.sub(r'Cosmos External Tracking Faceplate', 'Mặt nạ theo dõi gắn ngoài Cosmos', t, flags=re.I)
        t = re.sub(r'Cosmos Face Cushion 2 W Size', 'Đệm mặt Cosmos cỡ rộng (2 chiếc)', t, flags=re.I)
        t = re.sub(r'Cosmos Leather Cushion', 'Đệm da cao cấp cho VIVE Cosmos', t, flags=re.I)
        t = re.sub(r'Deluxe Pack For Vive Xr Elite', 'Bộ phụ kiện Deluxe cao cấp VIVE XR Elite', t, flags=re.I)
        t = re.sub(r'Disposable Hygiene Mask', 'Mặt nạ đệm vệ sinh dùng một lần', t, flags=re.I)
        t = re.sub(r'Extension Package 20m', 'Bộ cáp mở rộng 20m', t, flags=re.I)
        t = re.sub(r'Face Cushion Pro', 'Đệm mặt êm ái cho dòng VIVE Pro', t, flags=re.I)
        t = re.sub(r'Face Gasket And Rear Pad For Vive Focus Series Narrow', 'Đệm mặt & Đệm gáy VIVE Focus (Bản hẹp)', t, flags=re.I)
        t = re.sub(r'Face Gasket And Rear Pad For Vive Focus Series Wide', 'Đệm mặt & Đệm gáy VIVE Focus (Bản rộng)', t, flags=re.I)
        t = re.sub(r'Facial Tracker For Vive Focus Series', 'Thiết bị theo dõi khuôn mặt VIVE Focus', t, flags=re.I)
        t = re.sub(r'Headset Cable2 Pro', 'Dây cáp kết nối kính VIVE Pro 2', t, flags=re.I)
        t = re.sub(r'Link Box 2', 'Bộ chuyển đổi Link Box 2.0', t, flags=re.I)
        t = re.sub(r'Link Box Pro', 'Bộ chuyển đổi Link Box Pro', t, flags=re.I)
        t = re.sub(r'Link Box', 'Bộ chuyển đổi Link Box', t, flags=re.I)
        t = re.sub(r'Magnetic Charging Cable For Vive Ultimate Tracker', 'Dây sạc nam châm VIVE Ultimate Tracker', t, flags=re.I)
        t = re.sub(r'Magnetic Charging Dock For Vive Ultimate Tracker', 'Đế sạc nam châm VIVE Ultimate Tracker', t, flags=re.I)
        t = re.sub(r'Mini Displayport To Displayport Cable', 'Cáp chuyển Mini DisplayPort sang DisplayPort', t, flags=re.I)
        t = re.sub(r'Motion Tracker', 'Thiết bị theo dõi chuyển động VIVE Motion Tracker', t, flags=re.I)
        t = re.sub(r'Nose Rest For Vive Focus Series', 'Đệm mũi chắn sáng dòng VIVE Focus', t, flags=re.I)
        t = re.sub(r'Power Adapter', 'Củ sạc nguồn chính hãng VIVE', t, flags=re.I)
        t = re.sub(r'Trackstrap Plus', 'Dây đeo TrackStrap Plus kèm pin', t, flags=re.I)
        t = re.sub(r'Trackstrap', 'Dây đeo định vị TrackStrap', t, flags=re.I)
        t = re.sub(r'Tracker3', 'Thiết bị định vị vị trí VIVE Tracker 3.0', t, flags=re.I)
        t = re.sub(r'Ultimate Tracker', 'Thiết bị theo dõi toàn thân VIVE Ultimate Tracker', t, flags=re.I)
        t = re.sub(r'Usb C Cable', 'Cáp sạc và truyền dữ liệu USB-C', t, flags=re.I)
        t = re.sub(r'Vive Ultimate Tracker 3 Plus 1 Dongle Bundle', 'Bộ 3 VIVE Ultimate Tracker kèm USB Dongle', t, flags=re.I)
        t = re.sub(r'Vive Ultimate Tracker 3 Trackers And 1 Dongle', 'Combo 3 VIVE Ultimate Tracker + 1 Dongle', t, flags=re.I)
        t = re.sub(r'Vive Ultimate Tracker Trackstraps', 'Bộ dây đeo cho VIVE Ultimate Tracker', t, flags=re.I)
        t = re.sub(r'Vive Ultimate Tracker Usb C To Usb C Cable', 'Cáp USB-C cho VIVE Ultimate Tracker', t, flags=re.I)
        t = re.sub(r'Vive Wireless Adapter Clip For Vive Cosmos', 'Ngàm gắn bộ không dây VIVE Cosmos', t, flags=re.I)
        t = re.sub(r'Vive Wireless Adapter Clip For Vive Pro Series', 'Ngàm gắn bộ không dây VIVE Pro', t, flags=re.I)
        t = re.sub(r'Vive Wireless Adapter', 'Bộ phát không dây VIVE Wireless Adapter', t, flags=re.I)
        t = re.sub(r'Vive Wrist Tracker', 'Vòng đeo cổ tay định vị VIVE Wrist Tracker', t, flags=re.I)
        t = re.sub(r'Vive Xr Elite Battery Cradle', 'Khung gắn pin sau đầu VIVE XR Elite', t, flags=re.I)
        t = re.sub(r'Vive Xr Elite Face Gasket 2 0', 'Đệm mặt VIVE XR Elite 2.0', t, flags=re.I)
        t = re.sub(r'Vive Xr Elite Mr Gasket', 'Đệm mặt thực tế hỗn hợp MR VIVE XR Elite', t, flags=re.I)
        t = re.sub(r'Vive Xr Series Controller Left', 'Tay cầm VIVE XR Series (Bên trái)', t, flags=re.I)
        t = re.sub(r'Vive Xr Series Controller Right', 'Tay cầm VIVE XR Series (Bên phải)', t, flags=re.I)
        return f"Phụ kiện: {t}"

    # Xử lý các trang sản phẩm /product/...
    if path.startswith('product/'):
        prod_name = slug.replace('-', ' ').title()
        if 'Overview' in prod_name:
            base = path.split('/')[1].replace('-', ' ').title()
            return f"Kính thực tế ảo {base} - Tổng quan & Tính năng"
        if 'Specs' in prod_name:
            base = path.split('/')[1].replace('-', ' ').title()
            return f"Thông số kỹ thuật kính {base}"
        return f"Sản phẩm {prod_name}"

    # Xử lý các trang doanh nghiệp /business/...
    if path.startswith('business/'):
        b_name = slug.replace('-', ' ').title()
        b_name = re.sub(r'Isv', 'Đối tác phần mềm ISV', b_name, flags=re.I)
        b_name = re.sub(r'Program', 'Chương trình', b_name, flags=re.I)
        b_name = re.sub(r'Stories', 'Câu chuyện thành công của khách hàng', b_name, flags=re.I)
        b_name = re.sub(r'Contact Us', 'Liên hệ tư vấn giải pháp doanh nghiệp', b_name, flags=re.I)
        b_name = re.sub(r'Solutions', 'Giải pháp thực tế ảo doanh nghiệp', b_name, flags=re.I)
        return f"Doanh nghiệp: {b_name}"

    # Xử lý các trang lập trình viên /developer/...
    if path.startswith('developer/'):
        d_name = slug.replace('-', ' ').title()
        d_name = re.sub(r'Resources', 'Tài nguyên lập trình viên', d_name, flags=re.I)
        d_name = re.sub(r'Downloads', 'Tải SDK & Công cụ phát triển', d_name, flags=re.I)
        d_name = re.sub(r'Docs', 'Tài liệu hướng dẫn phát triển phần mềm', d_name, flags=re.I)
        return f"Nhà phát triển: {d_name}"

    # Xử lý các trang hỗ trợ /support/...
    if path.startswith('support/'):
        s_name = slug.replace('-', ' ').title()
        return f"Hướng dẫn hỗ trợ kỹ thuật: {s_name}"

    # Xử lý các trang tin tức /newsroom/...
    if path.startswith('newsroom/'):
        if re.match(r'^[0-9]{4}$', slug):
            return f"Thông cáo báo chí VIVE {slug}"
        if re.match(r'^[0-9]{4}-[0-9]{2}-[0-9]{2}', slug):
            parts = slug.split('-')
            return f"Tin tức HTC VIVE ({parts[2]}-{parts[1]}-{parts[0]})"
        return f"Phòng tin tức & Báo chí VIVE - {slug}"

    # Tiêu đề chung
    clean_title = slug.replace('-', ' ').title()
    clean_title = re.sub(r'Download', 'Tải xuống', clean_title, flags=re.I)
    clean_title = re.sub(r'Faq', 'Câu hỏi thường gặp (FAQ)', clean_title, flags=re.I)
    clean_title = re.sub(r'Terms', 'Điều khoản sử dụng', clean_title, flags=re.I)
    clean_title = re.sub(r'Privacy', 'Chính sách bảo mật', clean_title, flags=re.I)
    return clean_title

updates = []
for p in pages:
    new_title = translate_title(p)
    if new_title != p['title']:
        updates.append({'id': p['id'], 'old_title': p['title'], 'new_title': new_title, 'path': p['path']})

print(f"Total titles to update: {len(updates)}")
with open('scratch/title_updates.json', 'w', encoding='utf-8') as f:
    json.dump(updates, f, ensure_ascii=False, indent=2)

for u in updates[:20]:
    print(f"[{u['path']}] {u['old_title']} -> {u['new_title']}")
