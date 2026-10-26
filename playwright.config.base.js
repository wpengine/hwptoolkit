/**
 * Shared Playwright settings for plugin E2E tests.
 *
 * Each plugin's tests/e2e/playwright.config.js spreads this and adds its own globalSetup.
 * Relative paths (testDir) resolve against that plugin config, not this file.
 */
const path = require("path");
const { defineConfig, devices } = require("@playwright/test");

process.env.WP_ARTIFACTS_PATH ??= path.join(process.cwd(), "artifacts");
process.env.STORAGE_STATE_PATH ??= path.join(
	process.env.WP_ARTIFACTS_PATH,
	"storage-states/admin.json"
);

const baseUrl = new URL(process.env.WP_BASE_URL || "http://localhost:8889");

module.exports = defineConfig({
	reporter: process.env.CI ? [["github"]] : [["list"]],
	forbidOnly: !!process.env.CI,
	workers: 1,
	retries: process.env.CI ? 2 : 0,
	timeout: parseInt(process.env.TIMEOUT || "", 10) || 100_000,
	reportSlowTests: null,
	testDir: "./specs",
	outputDir: path.join(process.env.WP_ARTIFACTS_PATH, "test-results"),
	snapshotPathTemplate:
		"{testDir}/{testFileDir}/__snapshots__/{arg}-{projectName}{ext}",
	use: {
		baseURL: baseUrl.href,
		headless: true,
		viewport: {
			width: 960,
			height: 700,
		},
		ignoreHTTPSErrors: true,
		locale: "en-US",
		contextOptions: {
			reducedMotion: "reduce",
			strictSelectors: true,
		},
		storageState: process.env.STORAGE_STATE_PATH,
		actionTimeout: 10_000,
		trace: "retain-on-failure",
		screenshot: "only-on-failure",
		video: "on-first-retry",
	},
	webServer: {
		command: "npm run wp-env start",
		port: baseUrl.port,
		timeout: 120_000,
		reuseExistingServer: true,
	},
	projects: [
		{
			name: "chromium",
			use: { ...devices["Desktop Chrome"] },
		},
	],
});
