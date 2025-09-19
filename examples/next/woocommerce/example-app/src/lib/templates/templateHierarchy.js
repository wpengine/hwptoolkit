import {
  getTemplate,
  getPossibleTemplates,
  getAvailableTemplates,
} from "./templates.js";
import { SEED_QUERY } from "./seedQuery";
import { fetchGraphQL, client } from "../client";
import { gql } from "@apollo/client";

const SETTINGS_QUERY = gql`
  query HeaderSettingsQuery {
    generalSettings {
      title
    }
  }
`;

export async function uriToTemplate({ uri }) {
  const returnData = {
    uri,
    seedQuery: undefined,
    availableTemplates: undefined,
    possibleTemplates: undefined,
    template: undefined,
  };

  try {
    //WORKS!
    const seedQueryData = await fetchGraphQL(
      SEED_QUERY,
      {
        uri: uri,
      }
    );

    if (!seedQueryData?.data.nodeByUri) {
      console.error("HTTP/404 - Not Found in WordPress:", uri);
      returnData.template = { id: "404 Not Found", path: "/404" };
      return returnData;
    } else {
      returnData.seedQuery = seedQueryData.data.nodeByUri;
    }
    // const parsedQuery =
    //   typeof SEED_QUERY === "string"
    //     ? gql`
    //         ${SEED_QUERY}
    //       `
    //     : SEED_QUERY;

    // // Define all variables that the query expects
    // const variables = {
    //   uri: uri,
    //   id: 0, // Provide the default value for id
    //   asPreview: false, // Provide the default value for asPreview
    // };

    
    // //console.log("Variables:", JSON.stringify(variables, null, 2));

    // const { data, error } = await client.query({
    //   query: parsedQuery,
    //   variables: variables,
    //   fetchPolicy: "network-only",
    // });

    // if (error) {
    //   console.error("Apollo Client Error:", JSON.stringify(error, null, 2));
    // }

    // console.log("🚀 Apollo query result (data):", data);

    // returnData.seedQuery = data;

    const availableTemplates = await getAvailableTemplates();
    returnData.availableTemplates = availableTemplates;

    if (!availableTemplates || availableTemplates.length === 0) {
      console.error("No templates found");
      return returnData;
    }

    const possibleTemplates = getPossibleTemplates(
      seedQueryData.data.nodeByUri
    );
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
