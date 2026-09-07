import urllib.request
import json
import xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor

base_url = 'http://vive-vn.local'

# 1. Lay tat ca URLs tu cac sitemap cua Yoast
sitemaps = [
    f"{base_url}/page-sitemap.xml",
    f"{base_url}/post-sitemap.xml",
    f"{base_url}/product-sitemap.xml",
    f"{base_url}/product_cat-sitemap.xml"
]

all_sitemap_urls = set()
for sm in sitemaps:
    try:
        req = urllib.request.Request(sm, headers={'User-Agent': 'Mozilla/5.0'})
        content = urllib.request.urlopen(req, timeout=5).read()
        root = ET.fromstring(content)
        ns = {'ns': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
        for loc in root.findall('.//ns:loc', ns):
            if loc.text:
                all_sitemap_urls.add(loc.text.strip())
    except Exception as e:
        print(f"Error sitemap {sm}: {e}")

# 2. Lay tat ca URLs tu database pages
with open('scratch/all_db_pages.json', 'r', encoding='utf-8') as f:
    db_pages = json.load(f)

all_db_urls = set()
for p in db_pages:
    all_db_urls.add(p['permalink'])

# Chi kiem tra cac URLs local
test_urls = list(all_db_urls.union(all_sitemap_urls))
local_urls = [u for u in test_urls if u.startswith(base_url)]

print(f"Total Local URLs to test: {len(local_urls)}")
print(f"- From Database: {len(all_db_urls)}")
print(f"- From Sitemap: {len(all_sitemap_urls)}")

def check_one(url):
    status = 'UNKNOWN'
    final_url = url
    error = None
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=4)
        status = res.status
        final_url = res.geturl()
    except urllib.error.HTTPError as e:
        status = e.code
        error = str(e)
    except Exception as e:
        status = 'ERR'
        error = str(e)
        
    return {
        'url': url,
        'final_url': final_url,
        'status': status,
        'error': error,
        'in_db': url in all_db_urls,
        'in_sitemap': url in all_sitemap_urls
    }

with ThreadPoolExecutor(max_workers=4) as executor:
    results = list(executor.map(check_one, local_urls))

failures = [r for r in results if r['status'] != 200]

with open('scratch/fast_system_verification.json', 'w', encoding='utf-8') as f:
    json.dump({
        'total_tested': len(local_urls),
        'total_failures': len(failures),
        'total_passed': len(local_urls) - len(failures),
        'failures': failures
    }, f, ensure_ascii=False, indent=2)

print("\n--- HOAN TAT KIEM TRA TOAN BO HE THONG ---")
print(f"Tong so URL da quet: {len(local_urls)}")
print(f"So URL tra ve 200 OK: {len(local_urls) - len(failures)}")
print(f"So URL bi loi (404/ERR): {len(failures)}")
if failures:
    for f in failures[:10]:
        print(f"Failed: {f['url']} -> {f['status']}")
