import { request } from "@playwright/test";
import { RequestUtils } from "@wordpress/e2e-test-utils-playwright";

async function globalSetup(config) {
	const { baseURL, storageState } = config.projects[0].use;

	const requestContext = await request.newContext({
		baseURL,
	});

	const requestUtils = new RequestUtils(requestContext, {
		storageState,
	});

	await requestUtils.setupRest();

	await Promise.all([
		requestUtils.deleteAllPosts(),
		requestUtils.deleteAllPages(),
		requestUtils.resetPreferences(),
	]);

	await requestContext.dispose();
}

export default globalSetup;
