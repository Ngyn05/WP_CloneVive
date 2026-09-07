import os
import re

shop_us_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive\shop-us"

for root, dirs, files in os.walk(shop_us_dir):
    for f in files:
        if f.endswith(".htm") or f.endswith(".html"):
            fpath = os.path.join(root, f)
            with open(fpath, "r", encoding="utf-8", errors="ignore") as fp:
                content = fp.read()
            
            orig = content
            # Clean up og:site_name & schema
            content = content.replace("VIVE Store | United States", "VIVE Store Việt Nam")
            content = content.replace("VIVE Store | Hoa Kỳ", "VIVE Store Việt Nam")
            content = content.replace("Cửa hàng VIVE | Hoa Kỳ", "VIVE Store Việt Nam")
            
            if content != orig:
                with open(fpath, "w", encoding="utf-8") as fp:
                    fp.write(content)

print("Finished updating shop-us meta.")
