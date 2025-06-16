import { expect, test } from "@wordpress/e2e-test-utils-playwright";
import {
	defaultPostTypes,
	getPostObject,
	getSettingsField,
	goToPluginPage,
	installFaust,
	resetPluginSettings,
	switchToTab,
	uninstallFaust,
} from "../utils";
import {
	DEFAULT_FAUST_PREVIEW_URL,
	FAUST_FRONTEND_URL,
	HWP_SLUG,
} from "../constants";

test.describe("HWP Previews Faust Integration Test", () => {
	test.beforeAll(async ({ requestUtils }) => {
		await requestUtils.resetPreferences();
	});

	test.beforeEach(async ({ admin, page }) => {
		await installFaust(admin, page);

		// Set Faust frontend url
		await admin.visitAdminPage("/options-general.php?page=faustwp-settings");
		await page.locator("input#frontend_uri").fill(FAUST_FRONTEND_URL);
		await page.keyboard.press("Enter");
	});

	test.afterEach(async ({ admin, page }) => {
		await uninstallFaust(admin, page);
	});

	test("Faust settings applied properly", async ({
		page,
		admin,
		requestUtils,
	}) => {
		await requestUtils.activatePlugin(HWP_SLUG);
		await resetPluginSettings(admin);

		await goToPluginPage(admin);

		// Check Faust integration notification
		await expect(
			page.locator("#hwp_previews_faust_notice.notice"),
		).toBeVisible();

		// Settings should be enabled for all types with Faust preview URL
		for (const postType of defaultPostTypes) {
			await switchToTab(page, postType.label);

			const enabledCheckbox = getSettingsField(postType.key).enabledCheckbox;
			const previewUrlInput = getSettingsField(postType.key).previewUrlInput;

			await expect(page.locator(enabledCheckbox)).toBeChecked();
			await expect(page.locator(previewUrlInput)).toHaveValue(
				DEFAULT_FAUST_PREVIEW_URL,
			);
		}
	});

	test("Faust preview link continuity assured", async ({
		page,
		admin,
		requestUtils,
	}) => {
		// Create new page
		const post = await requestUtils.createPost(getPostObject("post"));

		// Check preview link on the table
		await admin.visitAdminPage(`/edit.php?post_type=post`);

		const faustPreviewLink = page
			.locator(`#post-${post.id} .view a`, {
				hasText: "Preview",
				exact: true,
			})
			.getAttribute("href");

		await requestUtils.activatePlugin(HWP_SLUG);
		await resetPluginSettings(admin);
		await admin.visitAdminPage(`/edit.php?post_type=post`);

		await expect(
			page.locator(`#post-${post.id} .view a`, {
				hasText: "Preview",
				exact: true,
			}),
		).toHaveAttribute("href", faustPreviewLink);
	});
});
