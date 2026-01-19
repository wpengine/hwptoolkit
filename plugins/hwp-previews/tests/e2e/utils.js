export const defaultPostTypes = [
	{ label: "Pages", key: "page" },
	{ label: "Media", key: "attachment" },
	{ label: "Posts", key: "post" },
];

export function getDefaultPreviewUrl(postKey) {
	return `https://localhost:3000/${postKey}?preview=true&post_id={ID}&name={slug}`;
}

export function getSettingsField(postKey) {
	return {
		enabledCheckbox: `input[name="hwp_previews_settings[${postKey}][enabled]"]`,
		iframeCheckbox: `input[name="hwp_previews_settings[${postKey}][in_iframe]"]`,
		previewUrlInput: `input[name="hwp_previews_settings[${postKey}][preview_url]"]`,
	};
}

export function getPostObject(postType, status = "draft") {
	return {
		title: `A new draft ${postType} to test previews`,
		content: `With this ${postType} we will test the HWP Previews plugin`,
		status: status,
	};
}

export async function switchToTab(page, tabName) {
	await page
		.locator("#wpbody-content")
		.getByRole("link", { name: tabName })
		.click();
}

export async function saveChanges(page) {
	await page.getByRole("button", { name: "Save Changes" }).click();
	await page.waitForSelector(".notice.notice-success");
}

export async function goToPluginPage(admin) {
	await admin.visitAdminPage("/options-general.php?page=hwp-previews");
}

export async function resetPluginSettings(admin) {
	await admin.visitAdminPage(
		"/options-general.php?page=hwp-previews&reset=true"
	);
}

export async function installFaust(admin, page) {
	const installSelector = '.install-now[data-slug="faustwp"]';
	const activateSelector = '.activate-now[data-slug="faustwp"]';

	await admin.visitAdminPage(
		"/plugin-install.php?s=faust&tab=search&type=term"
	);

	const installButton = page.locator(installSelector);

	if (await installButton.isVisible()) {
		await installButton.click();
		await page.waitForSelector(activateSelector, { timeout: 1000 * 90 });
		await page.locator(activateSelector).click();
	} else {
		await page.locator(activateSelector).click();
	}
}

export async function uninstallFaust(admin, page) {
	page.on("dialog", (dialog) => dialog.accept());

	await admin.visitAdminPage("/plugins.php");
	await page.locator("a#deactivate-faustwp").click();
	await page.locator("a#delete-faustwp").click();
}

export async function installACF(admin, page) {
	const installSelector = '.install-now[data-slug="advanced-custom-fields"]';
	const activateSelector = '.activate-now[data-slug="advanced-custom-fields"]';

	await admin.visitAdminPage(
		"/plugin-install.php?s=advanced-custom-fields&tab=search&type=term"
	);

	const installButton = page.locator(installSelector);

	if (await installButton.isVisible()) {
		await installButton.click();
		await page.waitForSelector(activateSelector, { timeout: 1000 * 90 });
		await page.locator(activateSelector).click();
	} else {
		await page.locator(activateSelector).click();
	}
}

export async function uninstallACF(admin, page) {
	// First, delete all ACF custom post types to clean up
	await admin.visitAdminPage("/edit.php?post_type=acf-post-type");

	// Check if there are any post types to delete
	const postTypeRows = await page
		.locator(".wp-list-table tbody tr:not(.no-items)")
		.count();

	if (postTypeRows > 0) {
		// Select all post types using the checkbox
		await page.locator("#cb-select-all-1").check();

		// Select "Move to Trash" from bulk actions dropdown
		await page.locator("#bulk-action-selector-bottom").selectOption("trash");

		// Click Apply button
		await page.locator("#doaction2").click();

		// Wait for the bulk action to complete
		await page.waitForLoadState("networkidle");
	}

	// Now uninstall the plugin
	page.on("dialog", (dialog) => dialog.accept());

	await admin.visitAdminPage("/plugins.php");
	await page.locator("a#deactivate-advanced-custom-fields").click();
	await page.locator("a#delete-advanced-custom-fields").click();
}

export async function createACFPostType(admin, page, postTypeConfig) {
	// Navigate to ACF Post Types page
	await admin.visitAdminPage("/edit.php?post_type=acf-post-type");

	// Click "Add New" button in the ACF header
	await page.getByRole("link", { name: "Add New", exact: true }).click();

	// Wait for the form to load
	await page.waitForSelector('input[name="acf_post_type[labels][name]"]');

	// Fill in the singular label first (this auto-generates the key)
	await page
		.locator('input[name="acf_post_type[labels][singular_name]"]')
		.fill(postTypeConfig.singularLabel);

	// Clear and fill in the post type key to ensure it's correct
	const postTypeKeyInput = page.locator(
		'input[name="acf_post_type[post_type]"]'
	);
	await postTypeKeyInput.clear();
	await postTypeKeyInput.fill(postTypeConfig.key);

	// Fill in the plural label
	await page
		.locator('input[name="acf_post_type[labels][name]"]')
		.fill(postTypeConfig.pluralLabel);

	// Save the post type using the form submit button
	await page.getByRole("button", { name: "Save Changes" }).click();
	await page.waitForSelector(".notice.notice-success", { timeout: 10000 });
}
