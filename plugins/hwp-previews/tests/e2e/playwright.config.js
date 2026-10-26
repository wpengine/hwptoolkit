import { defineConfig } from "@playwright/test";
import baseConfig from "../../../../playwright.config.base.js";

const config = defineConfig({
	...baseConfig,
	globalSetup: require.resolve("./config/global-setup.js"),
});

export default config;
