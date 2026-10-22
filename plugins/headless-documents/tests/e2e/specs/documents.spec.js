import { expect, test } from "@wordpress/e2e-test-utils-playwright";

const PLUGIN_SLUG = "headless-documents";

// A collection of its own per run, so reruns against the same site start empty.
const COLLECTION = `e2e-${Date.now()}`;

const documentPath = (id) =>
	`/headless/v1/collections/${COLLECTION}/documents/${id}`;

// requestUtils.rest() throws the parsed error body for non-2xx responses.
async function restError(promise) {
	try {
		await promise;
	} catch (error) {
		return error;
	}

	throw new Error("Expected the request to fail.");
}

test.describe("Headless Documents", () => {
	test.beforeAll(async ({ requestUtils }) => {
		await requestUtils.activatePlugin(PLUGIN_SLUG);
	});

	test("rejects a write based on a stale version", async ({
		requestUtils,
	}) => {
		const created = await requestUtils.rest({
			method: "PUT",
			path: documentPath("note"),
			data: { version: 0, data: { text: "first" } },
		});
		expect(created.version).toBe(1);

		const updated = await requestUtils.rest({
			method: "PUT",
			path: documentPath("note"),
			data: { version: 1, data: { text: "second" } },
		});
		expect(updated.version).toBe(2);

		const conflict = await restError(
			requestUtils.rest({
				method: "PUT",
				path: documentPath("note"),
				data: { version: 1, data: { text: "stale" } },
			}),
		);
		expect(conflict.code).toBe("headless_document_conflict");
		expect(conflict.data.document.data).toEqual({ text: "second" });

		const read = await requestUtils.rest({ path: documentPath("note") });
		expect(read).toMatchObject({ version: 2, data: { text: "second" } });
	});

	test("deletes only the current version", async ({ requestUtils }) => {
		await requestUtils.rest({
			method: "PUT",
			path: documentPath("to-delete"),
			data: { version: 0, data: { done: false } },
		});

		const conflict = await restError(
			requestUtils.rest({
				method: "DELETE",
				path: documentPath("to-delete"),
				params: { version: 5 },
			}),
		);
		expect(conflict.code).toBe("headless_document_conflict");

		const deleted = await requestUtils.rest({
			method: "DELETE",
			path: documentPath("to-delete"),
			params: { version: 1 },
		});
		expect(deleted.deleted).toBe(true);
	});

	test("lists saved documents on the Tools screen", async ({
		admin,
		page,
		requestUtils,
	}) => {
		await requestUtils.rest({
			method: "PUT",
			path: documentPath("listed"),
			data: { version: 0, data: { shown: true } },
		});

		await admin.visitAdminPage("tools.php", `page=${PLUGIN_SLUG}`);

		await expect(page.locator("h1")).toHaveText("Headless Documents");
		const row = page.locator("tr", { hasText: COLLECTION }).filter({
			hasText: "listed",
		});
		await expect(row).toBeVisible();

		await row.getByRole("link", { name: "listed" }).click();
		await expect(page.locator("h1")).toHaveText("Document");
		await expect(page.getByText('"shown": true')).toBeVisible();
	});
});
