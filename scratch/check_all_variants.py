import os
import re

theme_dir = r"c:\Users\hnguy\Local Sites\vive-vn\app\public\wp-content\themes\theme_vive"
products_dir = os.path.join(theme_dir, "shop-us", "products")

handles = [d for d in os.listdir(products_dir) if os.path.isdir(os.path.join(products_dir, d))]

has_variants = []
missing_variants = []

for h in handles:
    file_path = os.path.join(products_dir, h, "index.htm")
    if os.path.exists(file_path):
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        if "product-variants" in content or "product-variant" in content or "product-style" in content:
            has_variants.append(h)
        else:
            missing_variants.append(h)

print(f"Total products: {len(handles)}")
print(f"Has variants/selectors: {len(has_variants)}")
print(f"Missing variants: {len(missing_variants)} (e.g., single-variant accessories/subscriptions: {missing_variants[:10]})")
