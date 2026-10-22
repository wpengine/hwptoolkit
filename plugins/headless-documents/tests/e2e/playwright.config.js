import { defineConfig } from "@playwright/test";
import baseConfig from "@wordpress/scripts/config/playwright.config";

const config = defineConfig({
	...baseConfig,
	globalSetup: require.resolve("./config/global-setup.js"),
});

export default config;
