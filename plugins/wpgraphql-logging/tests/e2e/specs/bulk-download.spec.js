import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToLogsListPage,
	configureLogging,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

test.describe("Bulk Log Download", () => {
	test.beforeEach(async ({ admin, page }) => {
		await resetPluginSettings(admin);

		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");
	});

	test("downloads selected logs as a single CSV", async ({
		page,
		admin,
		request,
	}) => {
		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		// Create at least two log entries.
		for (let i = 0; i < 2; i++) {
			const response = await executeGraphQLQuery(request, GET_POSTS_QUERY);
			expect(response.ok()).toBeTruthy();
		}

		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		const logRows = page
			.locator("#the-list tr")
			.filter({ hasText: "GetPosts" });
		await expect(logRows.nth(1)).toBeVisible({ timeout: 10000 });

		const selectedIds = [];
		for (let i = 0; i < 2; i++) {
			const checkbox = logRows.nth(i).locator('input[name="log[]"]');
			await checkbox.check();
			selectedIds.push(await checkbox.inputValue());
		}

		await page
			.locator("#bulk-action-selector-top")
			.selectOption("bulk_download");

		const downloadPromise = page.waitForEvent("download");
		await page.locator("#doaction").click();
		const download = await downloadPromise;

		expect(download.suggestedFilename()).toMatch(
			/^graphql_logs_\d{4}-\d{2}-\d{2}_\d{6}\.csv$/
		);

		const fs = require("fs");
		const content = fs.readFileSync(await download.path(), "utf8");
		const lines = content.trim().split("\n");

		// One header row, then one row per selected log.
		expect(lines[0]).toContain("ID");
		expect(lines[0]).toContain("Message");
		for (const id of selectedIds) {
			expect(content).toMatch(new RegExp(`^${id},`, "m"));
		}
		expect(content).toContain("GetPosts");
	});
});
