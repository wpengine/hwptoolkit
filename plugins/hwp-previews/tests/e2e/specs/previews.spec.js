import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	getPostObject,
	getSettingsField,
	goToPluginPage,
	hwpSlug,
	resetHelperPluginSlug,
	saveChanges,
	switchToTab,
	testPreviewUrl,
	resetPluginSettings,
} from "../utils";

test.describe("HWP Previews Preview Link", () => {
	const typesToTest = ["post", "page"]; // TODO add CPT
	let contentIds = {};

	test.beforeAll(async ({ requestUtils }) => {
		// Create new page
		const newPost = await requestUtils.createPost(getPostObject("post"));
		contentIds.post = newPost.id;

		// Create new post
		const newPage = await requestUtils.createPage(getPostObject("page"));
		contentIds.page = newPage.id;

		await requestUtils.activatePlugin(hwpSlug);
		await requestUtils.activatePlugin(resetHelperPluginSlug);
	});

	test.beforeEach(async ({ admin }) => {
		await goToPluginPage(admin);
		await resetPluginSettings(admin);
	});

	typesToTest.forEach((postKey) => {
		test(`preview link for ${postKey} type should have a correct value`, async ({
			page,
			admin,
		}) => {
			await switchToTab(page, postKey);

			// Update the settings
			await page.locator(getSettingsField(postKey).enabledCheckbox).check();
			await page
				.locator(getSettingsField(postKey).previewUrlInput)
				.fill(testPreviewUrl);
			await saveChanges(page);

			// Check preview link on the table
			await admin.visitAdminPage(`/edit.php?post_type=${postKey}`);
			await expect(
				page.locator(`#post-${contentIds[postKey]} .view a`, {
					hasText: "Preview",
					exact: true,
				}),
			).toHaveAttribute("href", testPreviewUrl);

			// Check preview link on edit page
			await admin.editPost(contentIds[postKey]);
			await page.getByRole("button", { name: "View", exact: true }).click();
			await page.waitForSelector(".components-popover");
			await expect(
				page.getByRole("menuitem", { name: "Preview in new tab" }),
			).toHaveAttribute("href", testPreviewUrl);
		});
	});

	typesToTest.forEach((postKey) => {
		test(`preview link for ${postKey} type should have a correct value when HWP turned off`, async ({
			page,
			admin,
		}) => {
			await switchToTab(page, postKey);

			// Disable the preview setting
			await page.locator(getSettingsField(postKey).enabledCheckbox).uncheck();
			await saveChanges(page);

			// Check preview link on the table
			await admin.visitAdminPage(`/edit.php?post_type=${postKey}`);
			await expect(
				page.locator(`#post-${contentIds[postKey]} .view a`, {
					hasText: "Preview",
					exact: true,
				}),
			).not.toHaveAttribute("href", testPreviewUrl);

			// Check preview link on edit page
			await admin.editPost(contentIds[postKey]);
			await page.getByRole("button", { name: "View", exact: true }).click();
			await page.waitForSelector(".components-popover");
			await expect(
				page.getByRole("menuitem", { name: "Preview in new tab" }),
			).not.toHaveAttribute("href", testPreviewUrl);
		});
	});

	typesToTest.forEach((postKey) => {
		test(`iframe preview for ${postKey} type should work`, async ({
			page,
			admin,
		}) => {
			await switchToTab(page, postKey);

			// Update settings
			await page.locator(getSettingsField(postKey).enabledCheckbox).check();
			await page.locator(getSettingsField(postKey).iframeCheckbox).check();
			await page
				.locator(getSettingsField(postKey).previewUrlInput)
				.fill(testPreviewUrl);
			await saveChanges(page);

			// Visit the preview page
			await admin.visitAdminPage(`/edit.php?post_type=${postKey}`);
			const previewLink = page.locator(`#post-${contentIds[postKey]} .view a`, {
				hasText: "Preview",
				exact: true,
			});
			await previewLink.focus();
			await previewLink.click();
			await page.waitForLoadState("domcontentloaded");

			// Check if iframe included with the correct URL
			const iframe = page.locator("iframe.headless-preview-frame");
			await expect(iframe).toHaveAttribute("src", testPreviewUrl);
		});
	});
});

// TODO test if parameters being applied
// TODO test post_statuses_as_parent
