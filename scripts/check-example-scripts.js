#!/usr/bin/env node

/**
 * Check that every example follows the standard npm scripts described in
 * examples/README.md ("Running an example").
 *
 * Usage: node scripts/check-example-scripts.js
 *
 * Checks each example that has a .wp-env.json and a package.json:
 * - the standard scripts exist, and none of the legacy names remain
 * - every `npm run <script>` a script calls exists in the same package.json
 * - every `npm --prefix <dir>` points at a directory with a package.json
 * - `assets:zip`/`assets:unzip` exist only when wp-env/uploads.zip does, and
 *   `assets:unzip` extracts into wp-env/uploads for the zip's layout
 * - the example's README doesn't mention legacy script names, and its link to
 *   the shared docs resolves
 *
 * Exits with code 1 if any check fails.
 */

const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const STANDARD = ['setup', 'dev', 'app:dev', 'stop', 'clean', 'wp:start', 'db:import', 'db:export', 'db:query'];
const LEGACY = /\b(example:[\w:-]+|wp:stop|wp:destroy|wp:db:[\w-]+|wp:images:[\w-]+|wp:uploads:[\w-]+|app:install|backend:start)\b/;
const SHARED_DOCS_LINK = /\]\(([^)#\s]*README\.md)#running-an-example\)/g;

/**
 * Example directories: examples/<framework>/<name> and plugins/<plugin>/examples/<name>.
 */
function findExamples() {
	const dirs = [];
	const subdirs = (dir) =>
		fs.existsSync(dir)
			? fs.readdirSync(dir, { withFileTypes: true }).filter((d) => d.isDirectory()).map((d) => path.join(dir, d.name))
			: [];

	for (const framework of subdirs(path.join(ROOT, 'examples'))) dirs.push(...subdirs(framework));
	for (const plugin of subdirs(path.join(ROOT, 'plugins'))) dirs.push(...subdirs(path.join(plugin, 'examples')));

	return dirs.filter((dir) => fs.existsSync(path.join(dir, '.wp-env.json')) && fs.existsSync(path.join(dir, 'package.json')));
}

/**
 * Returns 'root' if the zip stores files under wp-env/uploads/, 'wp-env' if under uploads/.
 */
function zipLayout(zip) {
	const entries = execFileSync('unzip', ['-Z1', zip]).toString().split('\n');
	const first = entries.find((entry) => entry && !entry.startsWith('__MACOSX'));
	return first.startsWith('wp-env/') ? 'root' : 'wp-env';
}

function checkExample(dir) {
	const problems = [];
	const scripts = JSON.parse(fs.readFileSync(path.join(dir, 'package.json'), 'utf8')).scripts || {};

	for (const name of STANDARD) {
		if (!scripts[name]) problems.push(`missing "${name}" script`);
	}

	for (const [name, command] of Object.entries(scripts)) {
		if (LEGACY.test(name)) problems.push(`legacy script name "${name}"`);

		for (const [, called] of command.matchAll(/npm run ([\w:.-]+)/g)) {
			if (!scripts[called]) problems.push(`"${name}" runs missing script "${called}"`);
		}
		for (const [, prefix] of command.matchAll(/--prefix \.\/([\w-]+)/g)) {
			if (!fs.existsSync(path.join(dir, prefix, 'package.json'))) {
				problems.push(`"${name}" uses --prefix ./${prefix}, which has no package.json`);
			}
		}
	}

	const zip = path.join(dir, 'wp-env', 'uploads.zip');
	if (fs.existsSync(zip)) {
		if (!scripts['assets:zip'] || !scripts['assets:unzip']) {
			problems.push('has wp-env/uploads.zip but no assets:zip/assets:unzip scripts');
		} else {
			const extractsIntoWpEnv = /-d wp-env\b/.test(scripts['assets:unzip']);
			if ((zipLayout(zip) === 'root') === extractsIntoWpEnv) {
				problems.push('assets:unzip does not extract into wp-env/uploads for this zip layout');
			}
			if (!/npm run assets:unzip/.test(scripts.setup || '')) problems.push('setup does not run assets:unzip');
		}
	} else if (scripts['assets:zip'] || scripts['assets:unzip']) {
		problems.push('has assets scripts but no wp-env/uploads.zip');
	}

	const readme = path.join(dir, 'README.md');
	if (fs.existsSync(readme)) {
		const text = fs.readFileSync(readme, 'utf8');
		text.split('\n').forEach((line, i) => {
			const match = line.match(LEGACY);
			if (match && /npm|pnpm|`/.test(line)) problems.push(`README.md:${i + 1} mentions legacy script "${match[1]}"`);
		});
		for (const [, target] of text.matchAll(SHARED_DOCS_LINK)) {
			if (!fs.existsSync(path.resolve(dir, target))) problems.push(`README.md links to missing ${target}`);
		}
	}

	return problems;
}

let failures = 0;
const examples = findExamples();

for (const dir of examples) {
	const problems = checkExample(dir);
	if (problems.length) {
		failures += problems.length;
		console.log(`✗ ${path.relative(ROOT, dir)}`);
		for (const problem of problems) console.log(`    ${problem}`);
	}
}

if (failures) {
	console.log(`\n${failures} problem(s) in ${examples.length} examples. See examples/README.md#running-an-example.`);
	process.exit(1);
}
console.log(`✓ ${examples.length} examples follow the standard npm scripts.`);
