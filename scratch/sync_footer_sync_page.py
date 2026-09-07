from bs4 import BeautifulSoup
import re

# 1. Doc footer chuan tu pages/us/index.htm
with open('pages/us/index.htm', 'r', encoding='utf-8') as f:
    main_html = f.read()

main_soup = BeautifulSoup(main_html, 'html.parser')
standard_footer = main_soup.find('footer', class_='footer')

if not standard_footer:
    print("Error: Could not find standard footer in pages/us/index.htm")
    exit(1)

standard_footer_html = str(standard_footer)

# 2. Doc file sync.vive.com/home/index.htm
sync_file = 'footer-sites/sync.vive.com/home/index.htm'
with open(sync_file, 'r', encoding='utf-8') as f:
    sync_html = f.read()

sync_soup = BeautifulSoup(sync_html, 'html.parser')
sync_footer = sync_soup.find('div', id='footer')

if sync_footer:
    print("Found sync footer #footer, replacing with standard footer...")
    new_footer_soup = BeautifulSoup(standard_footer_html, 'html.parser')
    sync_footer.replace_with(new_footer_soup)
else:
    print("Could not find #footer by id, searching by class...")
    sync_footer_class = sync_soup.find('div', class_=re.compile(r'Footer__18_nF'))
    if sync_footer_class:
        new_footer_soup = BeautifulSoup(standard_footer_html, 'html.parser')
        sync_footer_class.replace_with(new_footer_soup)
    else:
        print("Footer element not found in sync html!")

# Dam bao co css revamp footer
if 'revamp-20/css/default.css' not in str(sync_soup.head) and 'dist_2020' not in str(sync_soup.head):
    css_links = BeautifulSoup('''
    <link rel="stylesheet" type="text/css" href="/static/app/dist_2020/other-styles.css?v=202608100232">
    <link rel="stylesheet" type="text/css" href="/static/app/dist_2020/original-styles.css?v=202608100232">
    <link rel="stylesheet" type="text/css" href="/static/app/dist_2020/new-styles.css?v=202608100232">
    <link rel="stylesheet" type="text/css" href="/media/filer_public/fed-assets/revamp-20/css/default.css?v=202605071200">
    ''', 'html.parser')
    sync_soup.head.append(css_links)

# Ghi lai file
with open(sync_file, 'w', encoding='utf-8') as f:
    f.write(str(sync_soup))

print("Sync page footer synchronized successfully!")
