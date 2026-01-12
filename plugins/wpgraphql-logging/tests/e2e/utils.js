/**
 * Reset WPGraphQL Logging settings
 */
export async function resetPluginSettings(admin) {
	await admin.visitAdminPage(
		"/admin.php?page=wpgraphql-logging&reset=true"
	);
}

/**
 * Navigate to WPGraphQL Logging settings page
 */
export async function goToLoggingSettingsPage(admin) {
	await admin.visitAdminPage("/admin.php?page=wpgraphql-logging");
}

/**
 * Navigate to WPGraphQL Logs list page
 */
export async function goToLogsListPage(admin) {
	await admin.visitAdminPage("/admin.php?page=wpgraphql-logging-view");
}

/**
 * Configure logging settings with common options
 */
export async function configureLogging(page, settings = {}) {
	const {
		enabled = true,
		dataSampling = "100",
		ipRestrictions = "",
		excludeQueries = "",
		logResponse = false,
		eventLogSelection = [],
	} = settings;

	// Enable/disable logging
	const enabledCheckbox = page.locator(
		'input[name="wpgraphql_logging_settings[basic_configuration][enabled]"]'
	);
	if (enabled) {
		await enabledCheckbox.check();
	} else {
		await enabledCheckbox.uncheck();
	}

	// Set data sampling
	await page
		.locator(
			'select[name="wpgraphql_logging_settings[basic_configuration][data_sampling]"]'
		)
		.selectOption(dataSampling);

	// Set IP restrictions
	if (ipRestrictions) {
		await page
			.locator(
				'input[name="wpgraphql_logging_settings[basic_configuration][ip_restrictions]"]'
			)
			.fill(ipRestrictions);
	}

	// Set exclude queries
	if (excludeQueries) {
		await page
			.locator(
				'input[name="wpgraphql_logging_settings[basic_configuration][exclude_query]"]'
			)
			.fill(excludeQueries);
	}

	// Set log response
	const logResponseCheckbox = page.locator(
		'input[name="wpgraphql_logging_settings[basic_configuration][log_response]"]'
	);
	if (logResponse) {
		await logResponseCheckbox.check();
	} else {
		await logResponseCheckbox.uncheck();
	}

	// Set event log selection (multi-select)
	if (eventLogSelection.length > 0) {
		const eventSelect = page.locator(
			'select[name="wpgraphql_logging_settings[basic_configuration][event_log_selection][]"]'
		);
		await eventSelect.selectOption(eventLogSelection);
	}

	await page.getByRole("button", { name: "Save Changes" }).click();
	await page.waitForSelector(".notice.notice-success");
}

/**
 * Execute a GraphQL query via the WordPress GraphQL endpoint
 */
export async function executeGraphQLQuery(request, query, variables = {}) {
	const response = await request.post("/graphql", {
		data: {
			query,
			variables,
		},
		headers: {
			"Content-Type": "application/json",
		},
	});

	return response;
}

/**
 * Get log details from a specific log entry
 */
export async function getLogDetails(page, logId) {
	// Navigate to the log detail page
	await page.goto(
		`/wp-admin/admin.php?page=wpgraphql-logging-view&action=view&log=${logId}`
	);

	// Extract log details from the table
	const details = {};
	const rows = await page.locator(".widefat.striped tbody tr").all();

	for (const row of rows) {
		const header = await row.locator("th").textContent();
		const value = await row.locator("td").textContent();
		details[header.trim()] = value.trim();
	}

	return details;
}

/**
 * Switch to a settings tab
 */
export async function switchToSettingsTab(page, tabName) {
	await page
		.locator("#wpbody-content")
		.getByRole("link", { name: "Data Management" })
		.click();
}

/**
 * Configure data management settings
 */
export async function configureDataManagement(page, settings = {}) {
	const {
		dataDeletionEnabled = false,
		dataRetentionDays = "30",
		dataSanitizationEnabled = false,
		dataSanitizationMethod = "recommended",
		dataSanitizationCustomFieldAnonymize = "",
		dataSanitizationCustomFieldRemove = "",
		dataSanitizationCustomFieldTruncate = "",
	} = settings;

	// Switch to Data Management tab
	await switchToSettingsTab(page, "Data Management");

	// Enable/disable data deletion
	const deletionCheckbox = page.locator(
		'input[name="wpgraphql_logging_settings[data_management][data_deletion_enabled]"]'
	);
	if (dataDeletionEnabled) {
		await deletionCheckbox.check();
	} else {
		await deletionCheckbox.uncheck();
	}

	// Set data retention days
	await page
		.locator(
			'input[name="wpgraphql_logging_settings[data_management][data_retention_days]"]'
		)
		.fill(dataRetentionDays);

	// Enable/disable data sanitization
	const sanitizationCheckbox = page.locator(
		'input[name="wpgraphql_logging_settings[data_management][data_sanitization_enabled]"]'
	);
	if (dataSanitizationEnabled) {
		await sanitizationCheckbox.check();
	} else {
		await sanitizationCheckbox.uncheck();
	}

	// Set sanitization method
	await page
		.locator(
			'select[name="wpgraphql_logging_settings[data_management][data_sanitization_method]"]'
		)
		.selectOption(dataSanitizationMethod);

	// Set custom field anonymize (if provided)
	if (dataSanitizationCustomFieldAnonymize) {
		await page
			.locator(
				'input[name="wpgraphql_logging_settings[data_management][data_sanitization_custom_field_anonymize]"]'
			)
			.fill(dataSanitizationCustomFieldAnonymize);
	}

	// Set custom field remove (if provided)
	if (dataSanitizationCustomFieldRemove) {
		await page
			.locator(
				'input[name="wpgraphql_logging_settings[data_management][data_sanitization_custom_field_remove]"]'
			)
			.fill(dataSanitizationCustomFieldRemove);
	}

	// Set custom field truncate (if provided)
	if (dataSanitizationCustomFieldTruncate) {
		await page
			.locator(
				'input[name="wpgraphql_logging_settings[data_management][data_sanitization_custom_field_truncate]"]'
			)
			.fill(dataSanitizationCustomFieldTruncate);
	}

	await page.getByRole("button", { name: "Save Changes" }).click();
	await page.waitForSelector(".notice.notice-success");
}
