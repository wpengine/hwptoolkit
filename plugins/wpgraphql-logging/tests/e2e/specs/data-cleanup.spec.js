import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	configureDataManagement,
	goToLoggingSettingsPage,
	resetPluginSettings,
	switchToSettingsTab,
} from "../utils";

test.describe("Data Management", () => {
	test.beforeEach(async ({ admin, page }) => {
		await resetPluginSettings(admin);

		// Go to settings page
		await goToLoggingSettingsPage(admin);
		await expect(page.locator("h1")).toHaveText("WPGraphQL Logging Settings");
	});

	test("configures data deletion and verifies cron job", async ({
		page,
		admin,
	}) => {
		await configureDataManagement(page, {
			dataDeletionEnabled: true,
			dataRetentionDays: "7",
			dataSanitizationEnabled: false,
		});

		// Reload the page to verify settings persisted
		await page.reload({ waitUntil: "networkidle" });

		await switchToSettingsTab(page, "Data Management");

		const deletionCheckbox = page.locator(
			'input[name="wpgraphql_logging_settings[data_management][data_deletion_enabled]"]'
		);
		await expect(deletionCheckbox).toBeChecked();

		const retentionInput = page.locator(
			'input[name="wpgraphql_logging_settings[data_management][data_retention_days]"]'
		);
		await expect(retentionInput).toHaveValue("7");

		// Verify cron job is scheduled with wp-crontrol plugin
		await admin.visitAdminPage("/tools.php?page=wp-crontrol");
		await expect(page.locator("h1")).toContainText("Cron Events");

		const cleanupHook = page
			.locator(".crontrol_hook")
			.filter({ hasText: "wpgraphql_logging_deletion_cleanup" });

		await expect(cleanupHook).toBeVisible();
	});
});
