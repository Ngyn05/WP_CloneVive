#!/usr/bin/env node
/** Read-only HTTP/HTML audit for URLs exposed by the Yoast sitemap. */

const base = new URL(process.argv[2] || "http://vive-vn.local/");
// Mirrored pages are expensive to render and Local's PHP worker pool is
// intentionally small. Run serially with a short pause so the audit itself
// cannot manufacture 502 responses by exhausting the worker.
const concurrency = 1;
const requestDelayMs = 150;

const decode = (value) => value
	.replaceAll("&amp;", "&")
	.replaceAll("&lt;", "<")
	.replaceAll("&gt;", ">")
	.replaceAll("&quot;", '"')
	.replaceAll("&#039;", "'");

async function request(url, redirect = "manual") {
	try {
		const response = await fetch(url, {
			redirect,
			headers: { "user-agent": "VIVE-Local-Prelaunch-Audit/1.0" },
		});
		return {
			url,
			status: response.status,
			location: response.headers.get("location") || "",
			type: response.headers.get("content-type") || "",
			body: await response.text(),
		};
	} catch (error) {
		return { url, status: 0, location: "", type: "", body: "", error: error.message };
	}
}

function locs(xml) {
	return [...xml.matchAll(/<loc>([\s\S]*?)<\/loc>/gi)].map((match) => decode(match[1].trim()));
}

async function pool(items, handler) {
	const results = new Array(items.length);
	let cursor = 0;
	async function worker() {
		while (cursor < items.length) {
			const index = cursor++;
			results[index] = await handler(items[index], index);
			if (requestDelayMs) await new Promise((resolve) => setTimeout(resolve, requestDelayMs));
		}
	}
	await Promise.all(Array.from({ length: Math.min(concurrency, items.length) }, worker));
	return results;
}

const sitemapIndexUrl = new URL("sitemap_index.xml", base).href;
const sitemapIndex = await request(sitemapIndexUrl);
const childSitemaps = locs(sitemapIndex.body);
const sitemapResponses = await pool(childSitemaps, (url) => request(url));
const urls = [...new Set(sitemapResponses.flatMap((item) => locs(item.body)))];
const pages = await pool(urls, (url) => request(url));

const issues = [];
const titleMap = new Map();
const descriptionMap = new Map();
const metrics = {
	sitemap_index_status: sitemapIndex.status,
	child_sitemaps: childSitemaps.length,
	sitemap_urls: urls.length,
	status_200: 0,
	redirects: 0,
	errors: 0,
	noindex: 0,
	missing_title: 0,
	missing_description: 0,
	missing_canonical: 0,
	missing_h1: 0,
	missing_og_image: 0,
	invalid_json_ld: 0,
};

for (const page of pages) {
	if (page.status === 200) metrics.status_200++;
	else if (page.status >= 300 && page.status < 400) metrics.redirects++;
	else metrics.errors++;
	if (page.status !== 200) issues.push({ type: "http", url: page.url, value: `${page.status} ${page.location}`.trim() });

	const titles = [...page.body.matchAll(/<title\b[^>]*>([\s\S]*?)<\/title>/gi)].map((m) => decode(m[1].replace(/<[^>]+>/g, "").trim()));
	const descriptions = [...page.body.matchAll(/<meta\b[^>]*\bname=["']description["'][^>]*\bcontent=["']([^"']*)["'][^>]*>/gi)].map((m) => decode(m[1].trim()));
	const canonicals = [...page.body.matchAll(/<link\b[^>]*\brel=["']canonical["'][^>]*\bhref=["']([^"']+)["'][^>]*>/gi)].map((m) => decode(m[1].trim()));
	const robots = [...page.body.matchAll(/<meta\b[^>]*\bname=["']robots["'][^>]*\bcontent=["']([^"']*)["'][^>]*>/gi)].map((m) => m[1].toLowerCase());
	const h1Count = (page.body.match(/<h1\b/gi) || []).length;
	const ogImageCount = (page.body.match(/<meta\b[^>]*\bproperty=["']og:image["']/gi) || []).length;

	if (titles.length !== 1 || !titles[0]) { metrics.missing_title++; issues.push({ type: "title-count", url: page.url, value: titles.length }); }
	if (titles[0] && /page not found|không tìm thấy trang/i.test(titles[0])) {
		issues.push({ type: "false-404-title", url: page.url, value: titles[0] });
	}
	if (descriptions.length !== 1 || !descriptions[0]) { metrics.missing_description++; issues.push({ type: "description-count", url: page.url, value: descriptions.length }); }
	if (canonicals.length !== 1 || !canonicals[0]) { metrics.missing_canonical++; issues.push({ type: "canonical-count", url: page.url, value: canonicals.length }); }
	if (h1Count < 1) { metrics.missing_h1++; issues.push({ type: "missing-h1", url: page.url, value: h1Count }); }
	if (ogImageCount < 1) { metrics.missing_og_image++; issues.push({ type: "missing-og-image", url: page.url, value: 0 }); }
	if (robots.some((value) => value.includes("noindex"))) { metrics.noindex++; issues.push({ type: "noindex", url: page.url, value: robots.join(" | ") }); }
	if (!/<html\b[^>]*\blang=["']vi(?:-|["'])/i.test(page.body)) issues.push({ type: "html-lang", url: page.url, value: "not vi" });

	for (const canonical of canonicals) {
		try {
			const expected = new URL(page.url);
			const actual = new URL(canonical, page.url);
			if (actual.origin !== expected.origin || actual.pathname !== expected.pathname) {
				issues.push({ type: "canonical-mismatch", url: page.url, value: actual.href });
			}
		} catch { issues.push({ type: "canonical-invalid", url: page.url, value: canonical }); }
	}

	for (const block of page.body.matchAll(/<script\b[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)) {
		try { JSON.parse(block[1]); }
		catch (error) { metrics.invalid_json_ld++; issues.push({ type: "json-ld", url: page.url, value: error.message }); }
	}

	if (titles[0]) titleMap.set(titles[0], [...(titleMap.get(titles[0]) || []), page.url]);
	if (descriptions[0]) descriptionMap.set(descriptions[0], [...(descriptionMap.get(descriptions[0]) || []), page.url]);
}

for (const [title, matchingUrls] of titleMap) {
	if (matchingUrls.length > 1) issues.push({ type: "duplicate-title", url: matchingUrls.join(" | "), value: title });
}
for (const [description, matchingUrls] of descriptionMap) {
	if (matchingUrls.length > 1) issues.push({ type: "duplicate-description", url: matchingUrls.join(" | "), value: description });
}

const robots = await request(new URL("robots.txt", base).href);
const notFound = await request(new URL("prelaunch-audit-definitely-missing/", base).href);
const report = {
	base: base.href,
	metrics,
	robots: { status: robots.status, body: robots.body },
	not_found_status: notFound.status,
	child_sitemap_results: sitemapResponses.map(({ url, status, type, error }) => ({ url, status, type, error })),
	issue_count: issues.length,
	issues,
};

if (process.argv.includes("--failed-json")) {
	console.log(JSON.stringify(pages.filter((page) => page.status !== 200).map((page) => page.url)));
} else if (process.argv.includes("--summary")) {
	const grouped = Object.groupBy ? Object.groupBy(issues, (issue) => issue.type) : issues.reduce((all, issue) => {
		(all[issue.type] ||= []).push(issue);
		return all;
	}, {});
	console.log(JSON.stringify({
		base: report.base,
		metrics: report.metrics,
		not_found_status: report.not_found_status,
		issue_types: Object.fromEntries(Object.entries(grouped).map(([type, entries]) => [type, entries.length])),
		critical_issues: issues.filter((issue) => ["http", "noindex", "title-count", "false-404-title", "description-count", "canonical-count", "canonical-mismatch", "json-ld", "missing-h1"].includes(issue.type)),
	}, null, 2));
} else {
	console.log(JSON.stringify(report, null, 2));
}
