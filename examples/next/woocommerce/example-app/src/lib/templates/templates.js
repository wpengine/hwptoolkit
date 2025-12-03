import { readdir } from "node:fs/promises";
import { join } from "node:path";
const TEMPLATE_PATH = "wp-templates";

export function getPossibleTemplates(node, uri) {
	let possibleTemplates = [];

	if (node.template?.templateName && node.template.templateName !== "Default") {
		possibleTemplates.push(`template-${node.template.templateName}`);
	}

	// Front page
	if (node.isFrontPage) {
		possibleTemplates.push("front-page");
	}

	// Blog page
	if (node.isPostsPage) {
		possibleTemplates.push("home");
	}

	// CPT archive page
	// eslint-disable-next-line no-underscore-dangle
	if (node.__typename === "ContentType" && node.isPostsPage === false) {
		if (node.name) {
			possibleTemplates.push(`archive-${node.name}`);
		}

		possibleTemplates.push("archive");
	}

	// Archive Page
	if (node.isTermNode) {
		const { taxonomyName } = node;

		switch (taxonomyName) {
			case "category": {
				if (node.slug) {
					possibleTemplates.push(`category-${node.slug}`);
				}

				if (node.databaseId) {
					possibleTemplates.push(`category-${node.databaseId}`);
				}

				possibleTemplates.push(`category`);

				break;
			}
			case "post_tag": {
				if (node.slug) {
					possibleTemplates.push(`tag-${node.slug}`);
				}

				if (node.databaseId) {
					possibleTemplates.push(`tag-${node.databaseId}`);
				}

				possibleTemplates.push(`tag`);

				break;
			}
			default: {
				if (taxonomyName) {
					if (node.slug) {
						possibleTemplates.push(`taxonomy-${taxonomyName}-${node.slug}`);
					}

					if (node.databaseId) {
						possibleTemplates.push(`taxonomy-${taxonomyName}-${node.databaseId}`);
					}

					possibleTemplates.push(`taxonomy-${taxonomyName}`);
				}

				possibleTemplates.push(`taxonomy`);
			}
		}

		possibleTemplates.push(`archive`);
	}

	if (node.userId) {
		if (node.name) {
			possibleTemplates.push(`author-${node.name?.toLocaleLowerCase()}`);
		}

		possibleTemplates.push(`author-${node.userId}`);
		possibleTemplates.push(`author`);
		possibleTemplates.push(`archive`);
	}

	// Singular page
	if (node.isContentNode) {
		if (node?.contentType?.node?.name !== "page" && node?.contentType?.node?.name !== "post") {
			if (node.contentType?.node?.name && node.slug) {
				possibleTemplates.push(`single-${node.contentType?.node?.name}-${node.slug}`);
			}

			if (node.contentType?.node?.name) {
				possibleTemplates.push(`single-${node.contentType?.node?.name}`);
			}
		}

		if (node?.contentType?.node?.name === "page") {
			if (node.slug) {
				possibleTemplates.push(`page-${node.slug}`);
			}

			if (node.databaseId) {
				possibleTemplates.push(`page-${node.databaseId}`);
			}

			possibleTemplates.push(`page`);
		}

		if (node?.contentType?.node?.name === "post") {
			if (node.slug) {
				possibleTemplates.push(`single-${node.contentType.node.name}-${node.slug}`);
			}

			possibleTemplates.push(`single-${node.contentType.node.name}`);
			possibleTemplates.push(`single`);
		}

		possibleTemplates.push(`singular`);
	}
	if (node.slug === "my-account" && uri.includes("/my-account/view-order/")) {
		possibleTemplates.push(`page-my-account-order`);
	}
	possibleTemplates.push("index");

	return possibleTemplates;
}

export async function getAvailableTemplates() {
	const files = await readdir(join("src", TEMPLATE_PATH));

	const templates = [];

	for (const file of files) {
		if (file === "index.js") {
			continue; // Skip the index file
		}

		const slug = file.replace(".js", "");

		templates.push({
			id: slug === "default" ? "index" : slug,
			path: join("/", TEMPLATE_PATH, file),
		});
	}

	return templates;
}

/* Find the first matching template from the possible templates list. 
You can also override or add more routes such as view-order in my account.
Used in /pages/[[...uri]].js to fetch the ID which must match in /wp-templates/index.js 
Returns the matching template config object { id, path } */

export function getTemplate(availableTemplates, possibleTemplates = [], uri) {
	for (const possibleTemplate of possibleTemplates) {
		let templateFromConfig = availableTemplates?.find((template) => template.id === possibleTemplate);
		
		if (uri.includes("/my-account/view-order/")) {
			console.log("te", templateFromConfig);
			templateFromConfig = availableTemplates?.find((template) => template.id === "page-my-account-order");
		}
		if (!templateFromConfig) {
			continue;
		}

		/* IMPORTANT: Example: If you have a filename such as taxonomy-product_cat or similar,
		 * we must modify the ID here so it matches in in /wp-templates/index.js
		 **/
		if (templateFromConfig.id && (templateFromConfig.id.includes("-") || templateFromConfig.id.includes("_"))) {
			templateFromConfig.id = templateFromConfig.id
				.replace(/_/g, "-")
				.split("-")
				.map((word, index) => (index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1)))
				.join("");
		}
		return templateFromConfig;
	}
}
