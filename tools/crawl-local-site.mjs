#!/usr/bin/env node
/** Crawl sitemap URLs plus recursively discovered same-origin HTML links. */

const base = new URL(process.argv[2] || "http://vive-vn.local/");
const maxPages = Number(process.argv.find((arg) => arg.startsWith("--max="))?.split("=")[1] || 1500);
const delay = 40;
const skipPath = /\/(?:wp-admin|wp-json|wp-content|wp-includes|xmlrpc\.php)(?:\/|$)|\.(?:css|js|mjs|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|mp4|webm|pdf|zip)(?:$|[?#])/i;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const decode = (value) => value.replaceAll("&amp;", "&").replaceAll("&#038;", "&").replaceAll("&quot;", '"').replaceAll("&#039;", "'");

async function get(url, redirect = "manual") {
	try {
		const response = await fetch(url, { redirect, headers: { "user-agent": "VIVE-Local-Full-Crawl/1.0" } });
		return { url, status: response.status, type: response.headers.get("content-type") || "", location: response.headers.get("location") || "", body: await response.text() };
	} catch (error) {
		return { url, status: 0, type: "", location: "", body: "", error: error.message };
	}
}

function locs(xml) {
	return [...xml.matchAll(/<loc>([\s\S]*?)<\/loc>/gi)].map((match) => decode(match[1].trim()));
}

function normalize(raw, current) {
	try {
		if (!raw || /^(?:#|javascript:|mailto:|tel:|data:)/i.test(raw)) return null;
		const url = new URL(decode(raw), current);
		if (url.origin !== base.origin || skipPath.test(url.pathname)) return null;
		url.hash = "";
		// Audit one canonical document per pathname. Commerce, tracking and form
		// query strings otherwise create an effectively unbounded crawl queue.
		url.search = "";
		return url.href;
	} catch { return null; }
}

const index = await get(new URL("sitemap_index.xml", base).href);
const children = locs(index.body);
const sitemapUrls = [];
for (const child of children) {
	const response = await get(child);
	sitemapUrls.push(...locs(response.body));
	await sleep(delay);
}

const queue = [base.href, ...sitemapUrls];
const seen = new Set();
const pages = [];
const assets = new Set();

while (queue.length && seen.size < maxPages) {
	const url = queue.shift();
	if (!url || seen.has(url)) continue;
	seen.add(url);
	const page = await get(url);
	pages.push(page);
	if (page.status === 200 && /text\/html/i.test(page.type)) {
		for (const match of page.body.matchAll(/<(?:a|form)\b[^>]*(?:href|action)=["']([^"']*)["']/gi)) {
			const found = normalize(match[1], url);
			if (found && !seen.has(found)) queue.push(found);
		}
		for (const match of page.body.matchAll(/<(?:script|link)\b[^>]*(?:src|href)=["']([^"']+)["']/gi)) {
			try {
				const asset = new URL(decode(match[1]), url);
				if (asset.origin === base.origin && /\.(?:css|js|mjs|woff2?|ttf|eot)(?:$|[?#])/i.test(asset.href)) assets.add(asset.href);
			} catch {}
		}
	}
	await sleep(delay);
}

const issues = [];
for (const page of pages) {
	if (page.status !== 200) issues.push({ type: "http", url: page.url, value: `${page.status} ${page.location}`.trim() });
	if (!/text\/html/i.test(page.type) || !page.body) continue;
	const title = decode((page.body.match(/<title\b[^>]*>([\s\S]*?)<\/title>/i)?.[1] || "").replace(/<[^>]+>/g, "").trim());
	if (!title) issues.push({ type: "missing-title", url: page.url });
	if (/page not found|không tìm thấy trang/i.test(title)) issues.push({ type: "false-404-title", url: page.url, value: title });
	if (page.status === 200 && !(page.body.match(/<h1\b/gi) || []).length) issues.push({ type: "missing-h1", url: page.url });
	if (page.status === 200 && !(page.body.match(/<meta\b[^>]*name=["']description["']/gi) || []).length) issues.push({ type: "missing-description", url: page.url });
	if (page.status === 200 && !(page.body.match(/<link\b[^>]*rel=["']canonical["']/gi) || []).length) issues.push({ type: "missing-canonical", url: page.url });
}

for (const assetUrl of assets) {
	const response = await get(assetUrl);
	if (response.status !== 200) issues.push({ type: "asset-http", url: assetUrl, value: response.status });
	await sleep(delay);
}

const grouped = issues.reduce((all, issue) => ((all[issue.type] ||= []).push(issue), all), {});
console.log(JSON.stringify({
	base: base.href,
	sitemap_urls: new Set(sitemapUrls).size,
	pages_checked: pages.length,
	assets_checked: assets.size,
	queue_remaining: queue.length,
	issue_types: Object.fromEntries(Object.entries(grouped).map(([type, entries]) => [type, entries.length])),
	issue_sample: issues.slice(0, 100),
}, null, 2));
