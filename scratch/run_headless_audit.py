import urllib.request
import re
import sys
from bs4 import BeautifulSoup

sys.stdout.reconfigure(encoding='utf-8')

test_cases = [
    {
        "name": "Chi tiết sản phẩm VIVE Eagle (Shop URL)",
        "url": "http://vive-vn.local/shop/products/vive-eagle/",
        "is_product": True
    },
    {
        "name": "Chi tiết sản phẩm Pantos Clear Lens Charcoal Black",
        "url": "http://vive-vn.local/shop/products/htc-vive-eagle-pantos-clear-lens-charcoal-black/",
        "is_product": True
    },
    {
        "name": "Đường dẫn WooCommerce cũ (/san-pham/vive-eagle/)",
        "url": "http://vive-vn.local/san-pham/vive-eagle/",
        "is_product": True
    },
    {
        "name": "Danh mục VIVE Eagle Out Now",
        "url": "http://vive-vn.local/shop/collections/vive-eagle-out-now/",
        "is_product": False
    },
    {
        "name": "Trang danh mục tổng (/shop/collections/)",
        "url": "http://vive-vn.local/shop/collections/",
        "is_product": False
    },
    {
        "name": "Trang chủ Cửa hàng (/shop/)",
        "url": "http://vive-vn.local/shop/",
        "is_product": False
    }
]

print("================================================================================")
print("             BÁO CÁO KIỂM THỬ HEADLESS TOÀN DIỆN (AUTOMATED AUDIT)               ")
print("================================================================================\n")

all_passed = True

for tc in test_cases:
    url = tc["url"]
    print(f"Kiểm tra: {tc['name']}")
    print(f"URL: {url}")
    
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        with urllib.request.urlopen(req, timeout=15) as resp:
            status = resp.status
            final_url = resp.geturl()
            html = resp.read().decode('utf-8', errors='ignore')
        
        soup = BeautifulSoup(html, 'html.parser')
        
        print(f"  • Mã phản hồi HTTP: {status} OK (URL cuối: {final_url})")
        print(f"  • Dung lượng tải: {len(html):,} bytes")
        
        # 1. Check raw USD prices
        raw_prices = re.findall(r'[\$]\s*\d+[\d\.,]*\s*(?:USD)?', html)
        if len(raw_prices) == 0:
            print("  • Giá tiền USD: [ĐẠT] Không còn giá USD thô (0)")
        else:
            print(f"  • Giá tiền USD: [CHƯA ĐẠT] Còn {len(raw_prices)} giá USD: {raw_prices[:3]}")
            all_passed = False
            
        # 2. Check "Còn hàng" badge
        con_hang = soup.find_all(string=re.compile(r'Còn hàng', re.I))
        if con_hang:
            print(f"  • Huy hiệu 'Còn hàng': [ĐẠT] Tìm thấy {len(con_hang)} vị trí")
        else:
            print("  • Huy hiệu 'Còn hàng': [CHƯA ĐẠT]")
            all_passed = False
            
        if tc["is_product"]:
            # 3. Check Style Selector
            has_style = bool(soup.select('#product-style-template--27611584627005__main, #vive-eagle-style-select, [data-name*="style"]'))
            print(f"  • Khối Chọn kiểu dáng (Style): {'[ĐẠT]' if has_style else '[CHƯA ĐẠT]'}")
            
            # 4. Check Lens Selector
            has_lens = bool(soup.select('#product-lens-template--27611584627005__main, #vive-eagle-lens-select, [data-name*="lens"]'))
            print(f"  • Khối Chọn tròng kính (Lens): {'[ĐẠT]' if has_lens else '[CHƯA ĐẠT]'}")
            
            # 5. Check Color Swatches
            swatches = soup.select('.product-variant__item-swatch, .single-swatch, [style*="background-color"]')
            has_color = len(swatches) >= 3 or bool(soup.select('[data-name*="color"]'))
            print(f"  • Khối Chọn màu sắc (Color Swatches): {'[ĐẠT] (' + str(len(swatches)) + ' swatches)' if has_color else '[CHƯA ĐẠT]'}")
            
            # 6. Check Quantity Stepper
            has_qty = bool(soup.select('.product-quantity, [name="quantity"], .qty-selector'))
            print(f"  • Bộ chọn Số lượng (Quantity Stepper): {'[ĐẠT]' if has_qty else '[CHƯA ĐẠT]'}")
            
            # 7. Check 3 Action Buttons
            has_zalo = bool(soup.find('a', string=re.compile(r'Zalo', re.I)) or "zalo.me" in html)
            has_buy = bool(soup.find('a', string=re.compile(r'Mua ngay', re.I)) or soup.select('[data-theme-vive-buy-slug]'))
            has_lead = bool(soup.select('form[data-vive-callback-form], [name="phone"]'))
            
            print(f"  • Nút 'TƯ VẤN QUA ZALO': {'[ĐẠT]' if has_zalo else '[CHƯA ĐẠT]'}")
            print(f"  • Nút 'MUA NGAY' (Popup lưu DB): {'[ĐẠT]' if has_buy else '[CHƯA ĐẠT]'}")
            print(f"  • Form 'Để lại SĐT tư vấn miễn phí': {'[ĐẠT]' if has_lead else '[CHƯA ĐẠT]'}")
            
            if not (has_style and has_lens and has_color and has_qty and has_zalo and has_buy and has_lead):
                all_passed = False
                
        print("  ------------------------------------------------------------------------------")
    except Exception as e:
        print(f"  • LỖI: {e}")
        all_passed = False
        print("  ------------------------------------------------------------------------------")

print(f"\nKẾT LUẬN TOÀN DIỆN: {'TẤT CẢ MỤC KIỂM TRA ĐỀU ĐẠT CHUẨN [PASS]' if all_passed else 'CÓ MỤC CẦN XỬ LÝ LẠI'}\n")
