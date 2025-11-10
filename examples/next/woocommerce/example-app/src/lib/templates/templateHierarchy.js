import { getTemplate, getPossibleTemplates, getAvailableTemplates } from "./templates.js";
import { SEED_QUERY } from "./seedQuery";
import { fetchGraphQL } from "../client";

export async function uriToTemplate({ uri }) {
	const returnData = {
		uri,
		seedQuery: undefined,
		availableTemplates: undefined,
		possibleTemplates: undefined,
		template: undefined,
	};

	try {
		const seedQueryData = await fetchGraphQL(SEED_QUERY, {
			uri: uri,
		});

		if (!seedQueryData?.data.nodeByUri) {
			console.error("HTTP/404 - Not Found in WordPress:", uri);
			returnData.template = { id: "404 Not Found", path: "/404" };
			return returnData;
		} else {
			returnData.seedQuery = seedQueryData.data.nodeByUri;
		}

		const availableTemplates = await getAvailableTemplates();
		returnData.availableTemplates = availableTemplates;

		if (!availableTemplates || availableTemplates.length === 0) {
			console.error("No templates found");
			return returnData;
		}

		const possibleTemplates = getPossibleTemplates(seedQueryData.data.nodeByUri);
		returnData.possibleTemplates = possibleTemplates;

		if (!possibleTemplates || possibleTemplates.length === 0) {
			console.error("No possible templates found");
			return returnData;
		}

		const template = getTemplate(availableTemplates, possibleTemplates);
		returnData.template = template;

		if (!template) {
			console.error("No template found for route");
		}

		return returnData;
	} catch (error) {
		console.error("❌ Error in uriToTemplate:", error);
		returnData.seedQuery = {
			loading: false,
			error: error,
			data: null,
		};
		return returnData;
	}
}
