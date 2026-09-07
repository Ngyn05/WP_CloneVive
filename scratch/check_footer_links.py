import json
import urllib.request
from urllib.parse import urljoin

with open('scratch/footer_check.json', 'r', encoding='utf-8') as f:
    links = json.load(f)

base_url = 'http://vive-vn.local'
report = []

seen = set()
for item in links:
    href = item['href']
    target = item['target']
    key = (target, href)
    if key in seen:
        continue
    seen.add(key)
    
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
    
    report.append({
        'name': target,
        'href': href,
        'full_url': full_url,
        'final_url': final_url,
        'status': status,
        'error': error
    })

with open('scratch/footer_status_report.json', 'w', encoding='utf-8') as f:
    json.dump(report, f, ensure_ascii=False, indent=2)

print(f"Total checked: {len(report)}")
for r in report:
    print(f"[{r['status']}] {r['name']} | Href: {r['href']} -> Final: {r['final_url']}")
