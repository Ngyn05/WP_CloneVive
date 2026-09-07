import json
import urllib.request
from urllib.parse import urljoin
from concurrent.futures import ThreadPoolExecutor

with open('scratch/footer_check.json', 'r', encoding='utf-8') as f:
    links = json.load(f)

base_url = 'http://vive-vn.local'
report = []
seen = set()

unique_items = []
for item in links:
    href = item['href']
    target = item['target']
    key = (target, href)
    if key in seen:
        continue
    seen.add(key)
    unique_items.append(item)

def check_link(item):
    href = item['href']
    target = item['target']
    full_url = urljoin(base_url, href)
    status = 'UNKNOWN'
    final_url = full_url
    error = None
    
    if full_url.startswith('http://vive-vn.local'):
        try:
            req = urllib.request.Request(full_url, headers={'User-Agent': 'Mozilla/5.0'})
            res = urllib.request.urlopen(req, timeout=5)
            status = res.status
            final_url = res.geturl()
        except urllib.error.HTTPError as e:
            status = e.code
            error = str(e)
        except Exception as e:
            status = 'ERR'
            error = str(e)
    else:
        status = 'EXTERNAL'
        
    return {
        'name': target,
        'href': href,
        'full_url': full_url,
        'final_url': final_url,
        'status': status,
        'error': error
    }

with ThreadPoolExecutor(max_workers=10) as executor:
    report = list(executor.map(check_link, unique_items))

with open('scratch/footer_status_report_v2.json', 'w', encoding='utf-8') as f:
    json.dump(report, f, ensure_ascii=False, indent=2)

print('Total links checked:', len(report))
not_200 = [r for r in report if r['status'] != 200]
print('Not 200 count:', len(not_200))
for r in not_200:
    print('Failed:', r['name'], r['href'], r['status'])
