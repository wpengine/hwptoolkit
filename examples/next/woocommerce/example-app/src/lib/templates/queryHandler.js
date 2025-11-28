import { client } from "../client";
import { gql } from "@apollo/client";

export async function fetchQueries({ queries, context, props }) {
  if (!queries || queries.length === 0) {
    console.error("No queries provided");
    return {};
  }

  let queryPromises = [];

  for (const query of queries) {
    if (!query.query) {
      console.error("Query is undefined");
      continue;
    }

    const queryName = query.name || query.query.definitions[0].name?.value;

    if (!queryName) {
      console.error("Query name is undefined, skipping query");
      continue;
    }

    const queryVariables = query.variables
      ? query.variables(context, props)
      : {};
    try {
 
      const parsedQuery =
        typeof query.query === "string"
          ? gql`
              ${query.query}
            `
          : query.query;

      const { data } = await client.query({
        query: parsedQuery,
        variables: queryVariables,
      });

      queryPromises.push({
        name: queryName,
        response: data,
      });
    } catch (error) {
      continue;
    }
  }

  const allSettledQueries = await Promise.all(queryPromises);

  const allResponses = {};

  for (const { name, response } of allSettledQueries) {
    allResponses[name] = response;
  }

  return allResponses;
}
