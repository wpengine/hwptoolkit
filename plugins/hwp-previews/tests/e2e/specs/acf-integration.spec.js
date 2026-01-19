import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import { HWP_SLUG, TEST_PREVIEW_URL } from "../constants";
import {
	createACFPostType,
	getSettingsField,
	goToPluginPage,
	installACF,
	resetPluginSettings,
	saveChanges,
	switchToTab,
	uninstallACF,
} from "../utils";

test.describe("HWP Previews ACF Integration Test", () => {
	const acfPostTypes = [
		{
			key: "book",
			pluralLabel: "Books",
			singularLabel: "Book",
		},
	];

	test.beforeAll(async ({ requestUtils }) => {
		await requestUtils.resetPreferences();
	});

	test.beforeEach(async ({ admin, page, requestUtils }) => {
		await resetPluginSettings(admin);
		await requestUtils.activatePlugin(HWP_SLUG);
		await installACF(admin, page);
	});

	test.afterEach(async ({ admin, page }) => {
		await uninstallACF(admin, page);
	});

	test("ACF custom post types appear in HWP Previews settings", async ({
		page,
		admin,
	}) => {
		// Create custom post types via ACF
		for (const postType of acfPostTypes) {
			await createACFPostType(admin, page, postType);
		}

		// Navigate to HWP Previews settings
		await goToPluginPage(admin);

		// Verify each ACF custom post type appears as a tab in settings
		for (const postType of acfPostTypes) {
			const tabLink = page
				.locator("#wpbody-content")
				.getByRole("link", { name: postType.pluralLabel });

			await expect(tabLink).toBeVisible();

			// Click on the tab to verify settings fields are present
			await switchToTab(page, postType.pluralLabel);

			// Verify settings fields exist for this custom post type
			const enabledCheckbox = getSettingsField(postType.key).enabledCheckbox;
			const previewUrlInput = getSettingsField(postType.key).previewUrlInput;
			const iframeCheckbox = getSettingsField(postType.key).iframeCheckbox;

			await expect(page.locator(enabledCheckbox)).toBeVisible();
			await expect(page.locator(previewUrlInput)).toBeVisible();
			await expect(page.locator(iframeCheckbox)).toBeVisible();
		}
	});

	test("ACF custom post types use HWP preview logic correctly", async ({
		page,
		admin,
		requestUtils,
	}) => {
		// Create a custom post type
		const testPostType = acfPostTypes[0]; // book
		await createACFPostType(admin, page, testPostType);

		// Configure HWP Previews for the custom post type
		await goToPluginPage(admin);

		await switchToTab(page, testPostType.pluralLabel);
		await page
			.locator(getSettingsField(testPostType.key).enabledCheckbox)
			.check();
		await page
			.locator(getSettingsField(testPostType.key).previewUrlInput)
			.fill(TEST_PREVIEW_URL);
		await saveChanges(page);

		// Create a post of the custom post type using REST API
		const customPost = await requestUtils.rest({
			method: "POST",
			path: `/wp/v2/${testPostType.key}`,
			data: {
				title: `Test ${testPostType.singularLabel}`,
				content: `Test content for ${testPostType.singularLabel}`,
				status: "draft",
			},
		});

		// Navigate to the custom post type list
		await admin.visitAdminPage(`/edit.php?post_type=${testPostType.key}`);

		// Verify preview link uses HWP preview URL
		await expect(
			page.locator(`#post-${customPost.id} .view a`, {
				hasText: "Preview",
				exact: true,
			})
		).toHaveAttribute("href", TEST_PREVIEW_URL);

		// Delete the post to not interfere with other tests
		await requestUtils.rest({
			method: "DELETE",
			path: `/wp/v2/${testPostType.key}/${customPost.id}`,
			data: {
				force: true,
			},
		});
	});

	test("ACF custom post types with iframe preview enabled", async ({
		page,
		admin,
		requestUtils,
	}) => {
		// Create custom post type
		const testPostType = acfPostTypes[0]; // book
		await createACFPostType(admin, page, testPostType);

		await goToPluginPage(admin);

		await switchToTab(page, testPostType.pluralLabel);
		await page
			.locator(getSettingsField(testPostType.key).enabledCheckbox)
			.check();
		await page
			.locator(getSettingsField(testPostType.key).iframeCheckbox)
			.check();
		await page
			.locator(getSettingsField(testPostType.key).previewUrlInput)
			.fill(TEST_PREVIEW_URL);
		await saveChanges(page);

		// Create a post of the custom post type
		const customPost = await requestUtils.rest({
			method: "POST",
			path: `/wp/v2/${testPostType.key}`,
			data: {
				title: `Test ${testPostType.singularLabel}`,
				content: `Test content for ${testPostType.singularLabel}`,
				status: "draft",
			},
		});

		// Navigate to the custom post type list and click preview
		await admin.visitAdminPage(`/edit.php?post_type=${testPostType.key}`);
		const previewLink = page.locator(`#post-${customPost.id} .view a`, {
			hasText: "Preview",
			exact: true,
		});
		await previewLink.focus();
		await previewLink.click();
		await page.waitForLoadState("domcontentloaded");

		// Verify iframe is present with correct URL
		const iframe = page.locator("iframe.headless-preview-frame");
		await expect(iframe).toHaveAttribute("src", TEST_PREVIEW_URL);

		// Delete the post to not interfere with other tests
		await requestUtils.rest({
			method: "DELETE",
			path: `/wp/v2/${testPostType.key}/${customPost.id}`,
			data: {
				force: true,
			},
		});
	});
});
