import urllib.request
import json
import xml.etree.ElementTree as ET
import time

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
        content = urllib.request.urlopen(req, timeout=10).read()
        root = ET.fromstring(content)
        # Namespace
        ns = {'ns': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
        for loc in root.findall('.//ns:loc', ns):
            if loc.text:
                all_sitemap_urls.add(loc.text.strip())
        print(f"Loaded sitemap {sm} -> total so far: {len(all_sitemap_urls)}")
    except Exception as e:
        print(f"Error fetching sitemap {sm}: {e}")

# 2. Lay tat ca URLs tu database pages
with open('scratch/all_db_pages.json', 'r', encoding='utf-8') as f:
    db_pages = json.load(f)

all_db_urls = set()
for p in db_pages:
    all_db_urls.add(p['permalink'])

print(f"Total Database Page URLs: {len(all_db_urls)}")
print(f"Total Sitemap URLs: {len(all_sitemap_urls)}")

# Hop nhat danh sach can kiem tra
test_urls = list(all_db_urls.union(all_sitemap_urls))
print(f"Total unique URLs to test: {len(test_urls)}")

results = []
failures = []

for idx, url in enumerate(test_urls):
    status = 'UNKNOWN'
    final_url = url
    error = None
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        res = urllib.request.urlopen(req, timeout=10)
        status = res.status
        final_url = res.geturl()
    except urllib.error.HTTPError as e:
        status = e.code
        error = str(e)
    except Exception as e:
        status = 'ERR'
        error = str(e)
        
    res_item = {
        'url': url,
        'final_url': final_url,
        'status': status,
        'error': error,
        'in_db': url in all_db_urls,
        'in_sitemap': url in all_sitemap_urls
    }
    results.append(res_item)
    if status != 200:
        failures.append(res_item)
        print(f"FAILED [{status}]: {url} -> Error: {error}")
    
    if (idx + 1) % 50 == 0:
        print(f"Checked {idx + 1}/{len(test_urls)} URLs...")
    time.sleep(0.02)

with open('scratch/full_system_verification.json', 'w', encoding='utf-8') as f:
    json.dump({
        'total_tested': len(test_urls),
        'total_failures': len(failures),
        'failures': failures,
        'results': results
    }, f, ensure_ascii=False, indent=2)

print("\n--- SUMMARY ---")
print(f"Total tested: {len(test_urls)}")
print(f"Total Passed (200 OK): {len(test_urls) - len(failures)}")
print(f"Total Failures: {len(failures)}")
