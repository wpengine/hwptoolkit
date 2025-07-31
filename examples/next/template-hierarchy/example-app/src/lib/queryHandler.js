import { client, fetchAllPaginated } from "./client";

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

    const queryResp = client.query(query.query, queryVariables);

    queryPromises.push(
      queryResp.then((response) => ({
        name: queryName,
        response,
      }))
    );
  }

  const allSettledQueries = await Promise.all(queryPromises);

  const allResponses = {};

  for (const { name, response } of allSettledQueries) {
    allResponses[name] = response;
  }

  return allResponses;
}
