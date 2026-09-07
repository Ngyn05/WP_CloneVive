import json
import re

with open('scratch/all_db_pages.json', 'r', encoding='utf-8') as f:
    pages = json.load(f)

print(f"Loaded {len(pages)} pages.")

# Kiem tra cac trang co the con chua duoc dich sang tieng Viet
untranslated = []
for p in pages:
    title = p['title']
    path = p['path']
    # Check if title has no Vietnamese characters and contains common English words
    has_vn = bool(re.search(r'[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]', title, re.IGNORECASE))
    
    # Neu khong co dau tieng Viet va khong phai la ten rieng thuong hieu nhu "VIVE XR Elite", "VIVE Flow", "VIVERSE"...
    if not has_vn:
        untranslated.append(p)

print(f"Potentially untranslated: {len(untranslated)}")
for u in untranslated[:30]:
    print(f"ID {u['id']} | Path: {u['path']} | Title: {u['title']}")
