import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	goToLoggingSettingsPage,
	goToLogsListPage,
	configureLogging,
	executeGraphQLQuery,
	resetPluginSettings,
} from "../utils";
import { GET_POSTS_QUERY } from "../constants";

const GET_SETTINGS_QUERY = `
	query GetSiteSettings {
		generalSettings {
			title
		}
	}
`;

test.describe("Search Logs", () => {
	test.beforeEach(async ({ admin, page, request }) => {
		await resetPluginSettings(admin);

		await goToLoggingSettingsPage(admin);
		await configureLogging(page, {
			enabled: true,
			dataSampling: "100",
			eventLogSelection: ["graphql_request_results"],
		});

		expect((await executeGraphQLQuery(request, GET_POSTS_QUERY)).ok()).toBeTruthy();
		expect((await executeGraphQLQuery(request, GET_SETTINGS_QUERY)).ok()).toBeTruthy();
	});

	test("finds logs by query text", async ({ page, admin }) => {
		await goToLogsListPage(admin);

		const rows = page.locator("#the-list tr");
		await expect(rows.filter({ hasText: "GetPosts" })).toHaveCount(1);
		await expect(rows.filter({ hasText: "GetSiteSettings" })).toHaveCount(1);

		const searchInput = page.locator("#wpgraphql-logging-search-input");
		await searchInput.fill("getsitesettings");
		await searchInput.press("Enter");

		// The search is redirected to GET so it stays in the pagination and sort links.
		await expect(page).toHaveURL(/[?&]s=getsitesettings(&|$)/);
		await expect(searchInput).toHaveValue("getsitesettings");
		await expect(rows.filter({ hasText: "GetSiteSettings" })).toHaveCount(1);
		await expect(rows.filter({ hasText: "GetPosts" })).toHaveCount(0);
	});

	test("combines search with the level filter", async ({ page, admin }) => {
		await goToLogsListPage(admin);

		await page.locator("#wpgraphql-logging-search-input").fill("GetPosts");
		await page.locator('select[name="level_filter"]').selectOption("error");
		await page.getByRole("button", { name: "Filter", exact: true }).click();

		await expect(page).toHaveURL(/level_filter=error/);
		await expect(page).toHaveURL(/[?&]s=GetPosts(&|$)/);
		await expect(page.locator('select[name="level_filter"]')).toHaveValue("error");
		await expect(
			page.locator('td.colspanchange:has-text("No items found.")')
		).toBeVisible();
	});

	test("shows no results for an unmatched search", async ({ page, admin }) => {
		await goToLogsListPage(admin);

		const searchInput = page.locator("#wpgraphql-logging-search-input");
		await searchInput.fill("NoSuchOperation");
		await searchInput.press("Enter");

		await expect(
			page.locator('td.colspanchange:has-text("No items found.")')
		).toBeVisible();
		// The search box stays visible so the search can be changed.
		await expect(searchInput).toHaveValue("NoSuchOperation");
	});
});
