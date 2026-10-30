import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToDashboardPage,
	configureLogging,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

test.describe("Logging Dashboard", () => {
	test.beforeEach(async ({ admin }) => {
		await resetPluginSettings(admin);
	});

	test("is listed in the GraphQL Logs menu", async ({ page, admin }) => {
		await admin.visitAdminPage("/index.php");
		const menuLink = page.locator(
			'#adminmenu a[href="admin.php?page=wpgraphql-logging-dashboard"]'
		);
		await expect(menuLink).toHaveText("Dashboard");
	});

	test("loads with summary tiles, chart and range links", async ({
		page,
		admin,
	}) => {
		await goToDashboardPage(admin);

		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Dashboard");
		await expect(page.locator(".wpgraphql-logging-dashboard__tile")).toHaveCount(4);
		await expect(
			page.locator('svg.wpgraphql-logging-chart__svg[role="img"]')
		).toBeVisible();

		// Default range is the last 7 days.
		await expect(
			page.locator('.wpgraphql-logging-dashboard__ranges a[aria-current="page"]')
		).toHaveText("Last 7 days");

		await page
			.locator(".wpgraphql-logging-dashboard__ranges a")
			.filter({ hasText: "Last 24 hours" })
			.click();
		await expect(page).toHaveURL(/range=24h/);
		await expect(
			page.locator('.wpgraphql-logging-dashboard__ranges a[aria-current="page"]')
		).toHaveText("Last 24 hours");
	});

	test("shows logged operations", async ({ page, admin, request }) => {
		await goToLoggingSettingsPage(admin);
		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		const response = await executeGraphQLQuery(request, GET_POSTS_QUERY);
		expect(response.ok()).toBeTruthy();

		// The reset helper clears cached dashboard figures, so the new entry shows up straight away.
		await goToDashboardPage(admin);

		const operationsPanel = page.locator(
			'section[aria-labelledby="wpgraphql-logging-operations-heading"]'
		);
		await expect(operationsPanel.locator("code", { hasText: "GetPosts" })).toBeVisible();
	});
});
