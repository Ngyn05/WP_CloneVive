import urllib.request
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

urls = [
    "http://vive-vn.local/shop/products/vive-eagle/",
    "http://vive-vn.local/san-pham/vive-eagle/"
]

for url in urls:
    print(f"\n==========================================")
    print(f"Testing URL: {url}")
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req) as resp:
            content = resp.read().decode('utf-8', errors='ignore')
            final_url = resp.geturl()

        print(f"Final URL: {final_url} | Status: {resp.status} | Length: {len(content)}")
        
        has_color = "Chọn màu" in content or "single-color" in content or "single-swatch" in content
        print(f"- Có khối Chọn màu sắc (Color swatches): {'[OK]' if has_color else '[MISSING]'}")
        
        has_style = "Chọn kiểu" in content or "Gọng Square" in content
        print(f"- Có khối Chọn kiểu dáng (Style): {'[OK]' if has_style else '[MISSING]'}")
        
        has_lens = "Chọn tròng" in content or "Tròng đổi màu" in content or "Transitions" in content
        print(f"- Có khối Chọn tròng kính (Lens): {'[OK]' if has_lens else '[MISSING]'}")
        
        has_qty = "Số lượng:" in content or "qty-selector" in content
        print(f"- Có bộ chọn Số lượng (Quantity): {'[OK]' if has_qty else '[MISSING]'}")
        
        has_stock = "Còn hàng" in content
        print(f"- Có chữ 'Còn hàng': {'[OK]' if has_stock else '[MISSING]'}")
        
    except Exception as e:
        print(f"Error on {url}: {e}")
