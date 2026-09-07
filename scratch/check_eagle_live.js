const https = require('https');

https.get('https://vive.com.vn/shop/products/vive-eagle/', (res) => {
    let html = '';
    res.on('data', chunk => html += chunk);
    res.on('end', () => {
        console.log('--- RESPONSE HEADERS ---');
        console.log('Build:', res.headers['x-theme-vive-build']);
        console.log('Render:', res.headers['x-theme-vive-render']);
        console.log('--- CHECKING ELEMENTS IN HTML ---');
        console.log('Has PREORDER text:', html.includes('PREORDER'));
        console.log('Has Thử ảo text:', html.includes('Thử ảo'));
        console.log('Has TƯ VẤN QUA ZALO text:', html.includes('TƯ VẤN QUA ZALO'));
        console.log('Has vive-woo-product__actions:', html.includes('vive-woo-product__actions'));
        
        // Print product-actions block in returned HTML
        const actionsMatch = html.match(/<div\b[^>]*class=["'][^"']*\bproduct-actions\b[\s\S]*?<\/div>\s*<\/div>/i);
        if (actionsMatch) {
            console.log('\n--- PRODUCT-ACTIONS FOUND IN HTML ---');
            console.log(actionsMatch[0].slice(0, 1000));
        } else {
            console.log('No product-actions found.');
        }
    });
}).on('error', err => {
    console.error('Error fetching:', err);
});
