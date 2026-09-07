import urllib.request
from bs4 import BeautifulSoup
import json

html = urllib.request.urlopen('http://vive-vn.local/sync/').read().decode('utf-8', errors='ignore')
soup = BeautifulSoup(html, 'html.parser')

footer_section = soup.find('footer', class_='footer')
has_standard_footer = bool(footer_section)

links = []
if footer_section:
    for a in footer_section.find_all('a'):
        links.append({'text': a.get_text(strip=True), 'href': a.get('href', '')})

result = {
    'title': soup.title.string if soup.title else '',
    'has_standard_footer': has_standard_footer,
    'footer_links_count': len(links),
    'sample_links': links[:15],
    'has_customer_support': 'HỖ TRỢ KHÁCH HÀNG' in html
}

with open('scratch/sync_footer_verification.json', 'w', encoding='utf-8') as f:
    json.dump(result, f, ensure_ascii=False, indent=2)

print("Verification result saved.")
