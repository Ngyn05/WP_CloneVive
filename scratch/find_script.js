const https = require('https');
const http = require('http');

function fetchUrl(url) {
    return new Promise((resolve, reject) => {
        const client = url.startsWith('https') ? https : http;
        client.get(url, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => resolve(data));
        }).on('error', reject);
    });
}

async function run() {
    const html = await fetchUrl('https://vive.com.vn/product/');
    const scriptRegex = /<script\b[^>]*src=["']([^"']+)["'][^>]*>/gi;
    let match;
    const scripts = [];
    while ((match = scriptRegex.exec(html)) !== null) {
        scripts.push(match[1]);
    }

    console.log(`Found ${scripts.length} external scripts in page.`);
    for (const src of scripts) {
        const fullUrl = src.startsWith('http') ? src : `https://vive.com.vn${src.startsWith('/') ? '' : '/'}${src}`;
        try {
            const jsContent = await fetchUrl(fullUrl);
            if (jsContent.includes('dynamic-product') || jsContent.includes('productTags-list')) {
                console.log(`\n>>> MATCH FOUND IN: ${fullUrl}`);
                if (jsContent.includes('dynamic-product')) console.log('  - Contains dynamic-product!');
                if (jsContent.includes('productTags-list')) console.log('  - Contains productTags-list!');
            }
        } catch (e) {
            console.error(`Failed to fetch ${fullUrl}:`, e.message);
        }
    }

    // Check inline scripts
    const inlineScriptRegex = /<script\b(?![^>]*src=)[^>]*>([\s\S]*?)<\/script>/gi;
    let inlineMatch;
    let idx = 0;
    while ((inlineMatch = inlineScriptRegex.exec(html)) !== null) {
        idx++;
        const content = inlineMatch[1];
        if (content.includes('dynamic-product') || content.includes('productTags-list')) {
            console.log(`\n>>> MATCH FOUND IN INLINE SCRIPT #${idx}:`);
            console.log(content.slice(0, 500));
        }
    }
}

run();
