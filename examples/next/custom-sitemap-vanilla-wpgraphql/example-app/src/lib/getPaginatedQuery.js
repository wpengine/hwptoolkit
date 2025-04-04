import { client } from "./client.js";

// This function is used to get paginated data from a GraphQL API.
export async function getPaginatedQuery(query, nodeKey, previousNodes = []) {
  const res = await client.query(query);

  const newNodes = [...previousNodes, ...res.data[nodeKey].nodes];

  // Check if the response has a next page
  if (res.data[nodeKey].pageInfo.hasNextPage) {
    return getPaginatedQuery(
      {
        query: query.query,
        variables: {
          after: res.data[nodeKey].pageInfo.endCursor,
          first: query.variables.first,
        },
      },
      nodeKey,
      newNodes
    );
  }

  return newNodes;
}
