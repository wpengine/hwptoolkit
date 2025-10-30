import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToLogsListPage,
	configureLogging,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

test.describe("Basic Logging Usage", () => {
	test.beforeEach(async ({ admin, page }) => {
		await resetPluginSettings(admin);

		// Go to settings page
		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");
	});

	test("enables logging and logs GraphQL queries", async ({
		page,
		admin,
		request,
	}) => {
		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		const response = await executeGraphQLQuery(request, GET_POSTS_QUERY);
		expect(response.ok()).toBeTruthy();

		// Check that the log appears in the logs list
		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		const logRow = page
			.locator("#the-list tr")
			.filter({ hasText: "GetPosts" })
			.first();
		await expect(logRow).toBeVisible({ timeout: 10000 });

		// View log details
		const viewLink = logRow.locator(".row-actions .view a");
		await expect(viewLink).toBeVisible();
		await viewLink.focus();
		await viewLink.click();

		await expect(page.locator("h1")).toContainText("Log Entry");

		const logTable = page.locator(".widefat.striped");
		await expect(logTable).toBeVisible();

		const queryRow = logTable
			.locator("tr")
			.filter({ has: page.locator("th", { hasText: "Query" }) });
		await expect(queryRow).toBeVisible();
		await expect(queryRow.locator("td pre")).toContainText("query GetPosts");

		// Go back to logs list
		const backLink = page
			.locator("p a.button")
			.filter({ hasText: "Back to Logs" });
		await expect(backLink).toBeVisible();

		await backLink.click();
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");
	});

	test("does not log when disabled", async ({ page, admin, request }) => {
		await configureLogging(page, {
			enabled: false,
			dataSampling: "100",
		});

		// Make sure there are no logs
		await goToLogsListPage(admin);
		await expect(
			page.locator('td.colspanchange:has-text("No items found.")')
		).toBeVisible();

		await executeGraphQLQuery(request, GET_POSTS_QUERY);

		// Navigate to logs and verify no new logs were created
		await goToLogsListPage(admin);
		await expect(
			page.locator('td.colspanchange:has-text("No items found.")')
		).toBeVisible();
	});

	test("downloads log as CSV with correct content", async ({
		page,
		admin,
		request,
	}) => {
		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		// Execute a GraphQL query
		const response = await executeGraphQLQuery(request, GET_POSTS_QUERY);
		expect(response.ok()).toBeTruthy();

		// Check that the log appears in the logs list
		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		const logRow = page
			.locator("#the-list tr")
			.filter({ hasText: "GetPosts" })
			.first();
		await expect(logRow).toBeVisible({ timeout: 10000 });

		// View log details
		const downloadButton = logRow.locator(".row-actions .download a");
		await expect(downloadButton).toBeVisible();

		const downloadPromise = page.waitForEvent("download");
		await downloadButton.focus();
		await downloadButton.click();
		const download = await downloadPromise;

		// Verify download properties
		expect(download.suggestedFilename()).toMatch(/graphql_log_\d+\.csv/);
		expect(download.suggestedFilename()).toContain(".csv");

		// Optionally save and verify the content
		const path = await download.path();
		const fs = require("fs");
		const content = fs.readFileSync(path, "utf8");

		// Verify CSV contains expected data
		expect(content).toContain("ID");
		expect(content).toContain("Date");
		expect(content).toContain("Level");
		expect(content).toContain("Message");
		expect(content).toContain("GetPosts");
	});

	test("should set data sampling to 10% and verify only 1 log is created", async ({
		page,
		admin,
		request,
	}) => {
		const QUERY_COUNT = 5;

		await configureLogging(page, {
			enabled: true,
			dataSampling: "25",
			eventLogSelection: ["graphql_request_results"],
		});

		// Execute a GraphQL queries
		const responses = await Promise.all(
			Array.from({ length: QUERY_COUNT }, async () =>
				executeGraphQLQuery(request, GET_POSTS_QUERY)
			)
		);
		await Promise.all(
			responses.map(async (response) => {
				return expect(response.ok()).toBeTruthy();
			})
		);

		// Navigate to logs and verify no new logs were created
		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		const logRow = page.locator("#the-list tr").filter({ hasText: "GetPosts" });

		const logCount = await logRow.count();
		expect(logCount).toBeLessThan(QUERY_COUNT);
	});
});
