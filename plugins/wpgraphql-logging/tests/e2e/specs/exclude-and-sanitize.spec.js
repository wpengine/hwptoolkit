import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToLogsListPage,
	configureLogging,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

test.describe("Exclude Sensitive Queries from Logging", () => {
	test.beforeEach(async ({ admin }) => {
		await resetPluginSettings(admin);
	});

	test("should exclude queries from logs when configured", async ({
		page,
		admin,
		request,
	}) => {
		// Set up logging with GetPosts excluded
		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");

		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
			excludeQueries: "GetPosts",
		});

		await expect(page.locator(".notice.notice-success")).toBeVisible();

		// Execute the excluded query
		const response = await executeGraphQLQuery(request, GET_POSTS_QUERY);
		expect(response.ok()).toBeTruthy();

		// Verify query is not logged
		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		await expect(
			page.locator('td.colspanchange:has-text("No items found.")')
		).toBeVisible();
	});

	test("should log queries when not excluded", async ({
		page,
		admin,
		request,
	}) => {
		// Set up logging without excluded queries
		await goToLoggingSettingsPage(admin);

		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
			excludeQueries: "",
		});

		await expect(page.locator(".notice.notice-success")).toBeVisible();

		// Execute query
		await executeGraphQLQuery(request, GET_POSTS_QUERY);

		// Navigate to logs and verify query is logged
		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		// Verify GetPosts query is logged
		const getPostsLog = page
			.locator("#the-list tr")
			.filter({ hasText: "GetPosts" });
		await expect(getPostsLog).toBeVisible({ timeout: 10000 });
	});

	// TODO add sanitization tests here
});
