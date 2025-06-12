export const hwpSlug = "hwp-previews";
export const resetHelperPluginSlug = "reset-hwp-previews-settings";
export const testPreviewUrl = "https://example.com/testPreview?preview=true";

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

	// TODO add waitfor
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
