import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	getDefaultPreviewUrl,
	goToPluginPage,
	defaultPostTypes,
	switchToTab,
	saveChanges,
	resetPluginSettings,
	getSettingsField,
} from "../utils";
import { HWP_SLUG, TEST_PREVIEW_URL } from "../constants";

test.describe("HWP Previews Admin Settings", () => {
	test.beforeAll(async ({ requestUtils }) => {
		await requestUtils.activatePlugin(HWP_SLUG);
	});

	test.beforeEach(async ({ admin }) => {
		await goToPluginPage(admin);
		await resetPluginSettings(admin);
	});

	test("display plugin settings page with all the tabs", async ({ page }) => {
		// Check plugin title
		await expect(page.locator("h1")).toHaveText("HWP Previews Settings");

		// Check tabs for each post type
		for (const postType of defaultPostTypes) {
			await switchToTab(page, postType.label);

			await expect(
				page
					.locator("#wpbody-content")
					.getByRole("link", { name: postType.label }),
			).toBeVisible();

			// Check if input has the correct default value
			await expect(page.locator("input.hwp-previews-url")).toHaveValue(
				`https://localhost:3000/${postType.key}?preview=true&post_id={ID}&name={slug}`,
			);
		}
	});

	test("switch between tabs correctly", async ({ page }) => {
		// Switch to each tab and check if it works
		for (const postType of defaultPostTypes) {
			await switchToTab(page, postType.label);

			await expect(
				page
					.locator("#wpbody-content")
					.getByRole("link", { name: postType.label }),
			).toBeVisible();
		}
	});

	test("URL input should have default value", async ({ page }) => {
		for (const postType of defaultPostTypes) {
			await switchToTab(page, postType.label);

			// Check if input has the correct default value
			await expect(page.locator("input.hwp-previews-url")).toHaveValue(
				getDefaultPreviewUrl(postType.key),
			);
		}
	});

	defaultPostTypes.forEach((postType) => {
		test(`save settings correctly for ${postType.label} type`, async ({
			page,
		}) => {
			// Test for each post type
			const enabledCheckbox = getSettingsField(postType.key).enabledCheckbox;
			const iframeCheckbox = getSettingsField(postType.key).iframeCheckbox;
			const previewUrlInput = getSettingsField(postType.key).previewUrlInput;

			// Go to specific post type tab
			await switchToTab(page, postType.label);

			// Update settings
			await page.locator(enabledCheckbox).check();
			await page.locator(iframeCheckbox).check();
			await page.locator(previewUrlInput).fill(TEST_PREVIEW_URL);

			// Save settings
			await saveChanges(page);

			// Verify success message
			await expect(page.getByText("Settings saved.")).toBeVisible();

			// Reload the page to ensure settings are applied
			await page.reload({ waitUntil: "networkidle" });

			// Verify settings were saved
			await expect(page.locator(enabledCheckbox)).toBeChecked();
			await expect(page.locator(iframeCheckbox)).toBeChecked();
			await expect(page.locator(previewUrlInput)).toHaveValue(TEST_PREVIEW_URL);
		});
	});

	test(`post statuses as parent option is being saved`, async ({ page }) => {
		const optionSelector = `input[name="hwp_previews_settings[page][post_statuses_as_parent]"]`;

		// Go to page tab
		await switchToTab(page, "page");

		// Update settings
		await page.locator(optionSelector).check();

		// Save settings
		await saveChanges(page);

		// Reload the page to ensure settings are applied
		await page.reload({ waitUntil: "networkidle" });

		// Verify settings were saved
		await expect(page.locator(optionSelector)).toBeChecked();
	});

	test(`URL parameter buttons works correctly`, async ({ page }) => {
		const urlInput = page.locator("input.hwp-previews-url");
		const paramButtons = await page
			.locator(".hwp-previews-tag-cloud button.hwp-previews-insert-tag")
			.all();

		for (const button of paramButtons) {
			const buttonText = await button.textContent();

			await button.click();

			const inputValue = await urlInput.inputValue();
			expect(inputValue).toContain(buttonText.trim());
		}
	});
});
