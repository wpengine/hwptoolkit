import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	resetPluginSettings,
	goToLoggingSettingsPage,
} from "../utils";

test.describe("WPGraphQL Logging Admin Notice", () => {
	test.beforeEach(async ({ admin, page }) => {
		await resetPluginSettings(admin); // Reset user meta data
		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");
	});

	test("admin notice is displayed", async ({
		page,
		admin,
	}) => {
		await goToLoggingSettingsPage(admin);
		await expect(
			page.locator("#wpgraphql-logging-admin-notice")
		).toBeVisible();

		await page.locator("#wpgraphql-logging-admin-notice.notice .notice-dismiss").click();
		await expect(
			page.locator("#wpgraphql-logging-admin-notice"),
		).not.toBeVisible();

		await page.reload();
		await expect(
			page.locator("#wpgraphql-logging-admin-notice"),
		).not.toBeVisible();
	});
});
