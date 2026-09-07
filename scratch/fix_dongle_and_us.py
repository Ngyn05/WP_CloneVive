import os
import re

shop_us_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive\shop-us"

modified_count = 0
for root, dirs, files in os.walk(shop_us_dir):
    for f in files:
        if f.endswith(".htm") or f.endswith(".html"):
            fpath = os.path.join(root, f)
            with open(fpath, "r", encoding="utf-8", errors="ignore") as fp:
                content = fp.read()
            
            orig = content
            
            # Replace Hoa Kỳ in titles and footers/branding
            content = re.sub(r'–\s*Cửa hàng VIVE\s*\|\s*Hoa Kỳ', '| VIVE Việt Nam', content)
            content = re.sub(r'–\s*VIVE Store\s*\|\s*Hoa Kỳ', '| VIVE Việt Nam', content)
            content = re.sub(r'\|\s*Hoa Kỳ', '| VIVE Việt Nam', content)
            content = re.sub(r'Cửa hàng VIVE \| Hoa Kỳ', 'VIVE Store Việt Nam', content)
            content = re.sub(r'VIVE Store \| Hoa Kỳ', 'VIVE Store Việt Nam', content)
            
            # Replace Dongle đa cổng -> Bộ chia nguồn đa cổng
            content = content.replace("Dongle đa cổng", "Bộ chia nguồn đa cổng")
            content = content.replace("Dongle nguồn đa cổng", "Bộ chia nguồn đa cổng")
            content = content.replace("dongle nguồn đa cổng", "bộ chia nguồn đa cổng")
            content = content.replace("dongle đa cổng", "bộ chia nguồn đa cổng")
            
            if content != orig:
                with open(fpath, "w", encoding="utf-8") as fp:
                    fp.write(content)
                modified_count += 1

print(f"Updated {modified_count} files in shop-us.")
