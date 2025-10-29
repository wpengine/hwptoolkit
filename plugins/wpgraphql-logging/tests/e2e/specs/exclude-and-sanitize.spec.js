import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToLogsListPage,
	configureLogging,
	configureDataManagement,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

test.describe("Query Filtering & Data Privacy", () => {
	test.beforeEach(async ({ admin }) => {
		await resetPluginSettings(admin);
	});

	test("excludes configured queries from logs", async ({
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

	test("sanitizes sensitive data in logs", async ({ page, admin, request }) => {
		// Set up logging settings and execute a GraphQL query
		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");

		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		await goToLoggingSettingsPage(admin);
		await configureDataManagement(page, {
			dataSanitizationEnabled: true,
			dataSanitizationMethod: "custom",
			dataSanitizationCustomFieldAnonymize: "request.app_context.viewer",
		});

		// Navigate to log details page
		await executeGraphQLQuery(request, GET_POSTS_QUERY);

		await goToLogsListPage(admin);
		await expect(page.locator("h1")).toContainText("WPGraphQL Logs");

		const logRow = page
			.locator("#the-list tr")
			.filter({ hasText: "GetPosts" })
			.first();
		await expect(logRow).toBeVisible({ timeout: 10000 });

		const viewLink = logRow.locator(".row-actions .view a");
		await expect(viewLink).toBeVisible();
		await viewLink.focus();
		await viewLink.click();

		await expect(page.locator("h1")).toContainText("Log Entry");

		const logTable = page.locator(".widefat.striped");
		const contextRow = logTable
			.locator("tr")
			.filter({ has: page.locator("th", { hasText: "Context" }) });

		await expect(contextRow).toBeVisible();

		// Verify sanitization in the content
		const contextContent = await contextRow.locator("td pre").textContent();

		expect(contextContent).toBeTruthy();
		expect(contextContent).toContain('"viewer": "***"');
	});
});
