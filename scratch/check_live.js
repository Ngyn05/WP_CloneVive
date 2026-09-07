const https = require('https');

https.get('https://vive.com.vn/product/', (res) => {
    let html = '';
    res.on('data', chunk => html += chunk);
    res.on('end', () => {
        const match = html.match(/<div class="productTags-list">([\s\S]*?)<\/div>/i);
        if (match) {
            console.log('--- FOUND PRODUCT TAGS LIST ---');
            console.log(match[1]);
        } else {
            console.log('No productTags-list found in HTML');
        }
    });
}).on('error', err => {
    console.error('Error fetching:', err);
});
