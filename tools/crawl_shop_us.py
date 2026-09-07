import os
import json
import re
import time
import urllib.request
import urllib.error
import random

BASE_URL = "https://shop-us.vive.com"
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'en-US,en;q=0.9',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
    'Sec-Fetch-Dest': 'document',
    'Sec-Fetch-Mode': 'navigate',
    'Sec-Fetch-Site': 'same-origin',
    'Sec-Fetch-User': '?1',
    'Upgrade-Insecure-Requests': '1'
}

OUTPUT_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "shop-us"))
COLLECTIONS_DIR = os.path.join(OUTPUT_DIR, "collections")
PRODUCTS_DIR = os.path.join(OUTPUT_DIR, "products")

os.makedirs(COLLECTIONS_DIR, exist_ok=True)
os.makedirs(PRODUCTS_DIR, exist_ok=True)

def fetch_url(url, is_json=False, retries=5):
    for attempt in range(retries):
        try:
            req = urllib.request.Request(url, headers=HEADERS)
            with urllib.request.urlopen(req, timeout=30) as resp:
                content = resp.read()
                if is_json:
                    return json.loads(content.decode('utf-8', errors='ignore'))
                return content.decode('utf-8', errors='ignore')
        except urllib.error.HTTPError as e:
            if e.code == 429:
                wait_time = (attempt + 1) * 3 + random.uniform(1, 3)
                print(f"[WARN] HTTP 429 rate limited on {url}. Sleeping {wait_time:.1f}s before retry {attempt+1}/{retries}...")
                time.sleep(wait_time)
            elif attempt < retries - 1:
                time.sleep(2)
            else:
                print(f"[ERROR] Failed to fetch {url} (HTTP {e.code}): {e}")
                return None
        except Exception as e:
            if attempt < retries - 1:
                time.sleep(2)
            else:
                print(f"[ERROR] Failed to fetch {url}: {e}")
                return None
    return None

def fetch_all_products_json():
    print("[INFO] Fetching all products via Shopify JSON API...")
    products = []
    page = 1
    while True:
        url = f"{BASE_URL}/products.json?limit=250&page={page}"
        data = fetch_url(url, is_json=True)
        if not data or 'products' not in data:
            break
        page_products = data['products']
        if not page_products:
            break
        products.extend(page_products)
        print(f"[INFO] Page {page}: fetched {len(page_products)} products (Total: {len(products)})")
        page += 1
    return products

def crawl_missing_products(products):
    total = len(products)
    results = []
    success_count = 0
    
    print(f"\n[INFO] Checking and downloading HTML for all {total} products...")
    
    for idx, product in enumerate(products, 1):
        handle = product.get('handle')
        title = product.get('title', 'Unknown')
        prod_id = product.get('id')
        
        prod_dir = os.path.join(PRODUCTS_DIR, handle)
        os.makedirs(prod_dir, exist_ok=True)
        
        # Save JSON
        json_path = os.path.join(prod_dir, "product.json")
        with open(json_path, "w", encoding="utf-8") as f:
            json.dump(product, f, ensure_ascii=False, indent=2)
            
        html_path = os.path.join(prod_dir, "index.htm")
        html_url = f"{BASE_URL}/products/{handle}"
        
        # If valid html exists (> 1000 bytes), skip fetching
        if os.path.exists(html_path) and os.path.getsize(html_path) > 1000:
            status = f"ALREADY EXISTS ({os.path.getsize(html_path)} bytes)"
            success_count += 1
            print(f"[{idx}/{total}] [CACHED] '{title}' ({handle})")
        else:
            time.sleep(random.uniform(0.6, 1.2)) # polite rate limiting
            html_content = fetch_url(html_url)
            if html_content and len(html_content) > 1000:
                with open(html_path, "w", encoding="utf-8") as f:
                    f.write(html_content)
                status = f"OK ({len(html_content)} bytes)"
                success_count += 1
                print(f"[{idx}/{total}] [DOWNLOADED] '{title}' ({handle}) -> {len(html_content)} bytes")
            else:
                status = "FAILED HTML"
                print(f"[{idx}/{total}] [FAILED] '{title}' ({handle})")
        
        results.append({
            "id": prod_id,
            "title": title,
            "handle": handle,
            "url": html_url,
            "variants_count": len(product.get('variants', [])),
            "images_count": len(product.get('images', [])),
            "status": status
        })
        
    return results, success_count

def crawl_missing_collections():
    print("\n[INFO] Checking Collections...")
    main_collections_url = f"{BASE_URL}/collections"
    main_html_path = os.path.join(COLLECTIONS_DIR, "index.htm")
    if not os.path.exists(main_html_path) or os.path.getsize(main_html_path) < 1000:
        main_html = fetch_url(main_collections_url)
        if main_html:
            with open(main_html_path, "w", encoding="utf-8") as f:
                f.write(main_html)
    else:
        with open(main_html_path, "r", encoding="utf-8") as f:
            main_html = f.read()

    # Extract all collection URLs
    collection_handles = set()
    if main_html:
        matches = re.findall(r'href=[\'"]/collections/([^\'"?#]+)[\'"]', main_html)
        for m in matches:
            if m and m != 'collections':
                collection_handles.add(m)
    
    standard_cols = [
        "all", "headsets", "accessories", "vive-eagle-out-now", "vive-eagle-1",
        "focus-series", "xr-series", "pro-series", "vive-flow", "vive-ultimate-tracker",
        "vive-mars-series", "vive-focus-series-for-business", "vive-pro-series-for-business",
        "vive-xr-elite-for-business", "vive-flow-for-business", "on-sale", "battery_included"
    ]
    for c in standard_cols:
        collection_handles.add(c)
    
    collections_metadata = []
    for c_handle in sorted(collection_handles):
        c_url = f"{BASE_URL}/collections/{c_handle}"
        c_dir = os.path.join(COLLECTIONS_DIR, c_handle)
        os.makedirs(c_dir, exist_ok=True)
        c_path = os.path.join(c_dir, "index.htm")
        
        if os.path.exists(c_path) and os.path.getsize(c_path) > 1000:
            c_status = f"ALREADY EXISTS ({os.path.getsize(c_path)} bytes)"
        else:
            time.sleep(random.uniform(0.5, 1.0))
            c_html = fetch_url(c_url)
            if c_html:
                with open(c_path, "w", encoding="utf-8") as f:
                    f.write(c_html)
                c_status = f"OK ({len(c_html)} bytes)"
            else:
                c_status = "FAILED"
        
        print(f"  - Collection: {c_handle} -> {c_status}")
        collections_metadata.append({
            "handle": c_handle,
            "url": c_url,
            "status": c_status
        })
    
    with open(os.path.join(COLLECTIONS_DIR, "collections_list.json"), "w", encoding="utf-8") as f:
        json.dump(collections_metadata, f, ensure_ascii=False, indent=2)

def main():
    print("==================================================")
    print("        RESUMABLE SHOP-US VIVE CRAWLER            ")
    print("==================================================")
    
    # 1. Crawl Collections
    crawl_missing_collections()
    
    # 2. Fetch all products JSON
    products = fetch_all_products_json()
    master_json_path = os.path.join(OUTPUT_DIR, "all_products.json")
    with open(master_json_path, "w", encoding="utf-8") as f:
        json.dump(products, f, ensure_ascii=False, indent=2)
    print(f"[INFO] Saved master all_products.json ({len(products)} products)")
    
    # 3. Crawl missing product detail HTMLs
    results, success_count = crawl_missing_products(products)
    
    # Save summary
    summary_path = os.path.join(OUTPUT_DIR, "products_summary.json")
    with open(summary_path, "w", encoding="utf-8") as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
        
    # Also save a clean CSV summary for easy view
    csv_path = os.path.join(OUTPUT_DIR, "products_summary.csv")
    import csv
    with open(csv_path, "w", newline="", encoding="utf-8-sig") as f:
        writer = csv.writer(f)
        writer.writerow(["ID", "Title", "Handle", "URL", "Variants Count", "Images Count", "Status"])
        for r in results:
            writer.writerow([r["id"], r["title"], r["handle"], r["url"], r["variants_count"], r["images_count"], r["status"]])
    
    print("\n==================================================")
    print(f"  CRAWL RESULT: {success_count}/{len(products)} Products OK!")
    print(f"  Products directory: {PRODUCTS_DIR}")
    print(f"  Collections directory: {COLLECTIONS_DIR}")
    print(f"  Master JSON: {master_json_path}")
    print(f"  Summary CSV: {csv_path}")
    print("==================================================")

if __name__ == "__main__":
    main()
