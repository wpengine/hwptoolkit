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
		"/options-general.php?page=hwp-previews&reset=true",
	);
}

export async function installFaust(admin, page) {
	const installSelector = '.install-now[data-slug="faustwp"]';
	const activateSelector = '.activate-now[data-slug="faustwp"]';

	await admin.visitAdminPage(
		"/plugin-install.php?s=faust&tab=search&type=term",
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
