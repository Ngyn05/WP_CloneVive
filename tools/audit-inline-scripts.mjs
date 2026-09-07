#!/usr/bin/env node
/** Validate executable inline JavaScript without running the page. */

import vm from "node:vm";

const url = process.argv[2] || "http://vive-vn.local/";
const html = await (await fetch(url)).text();
let index = 0;
let failures = 0;

for (const match of html.matchAll(/<script(?<attrs>[^>]*)>(?<code>[\s\S]*?)<\/script>/gi)) {
	index++;
	const attrs = match.groups.attrs;
	const code = match.groups.code.trim();
	const type = attrs.match(/\btype=["']([^"']+)/i)?.[1]?.toLowerCase() || "";
	if (!code || (type && !["text/javascript", "application/javascript"].includes(type))) continue;
	try {
		new vm.Script(code, { filename: `inline-script-${index}.js` });
	} catch (error) {
		failures++;
		console.log(JSON.stringify({ index, type, error: error.message, start: code.slice(0, 240) }));
	}
}

console.log(JSON.stringify({ url, executable_inline_scripts: index, failures }));
