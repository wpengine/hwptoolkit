import { Client, fetchExchange } from "@urql/core";
export { gql } from "@urql/core";
// import { persistedExchange } from "@urql/exchange-persisted";

const GRAPHQL_URL = "index.php?graphql";
const graphqlApi = new URL(GRAPHQL_URL, "http://localhost:8888").href;

/**
 * This is a very basic URQL client setup.
 * It uses the fetchExchange to make network requests.
 *
 * You can add more exchanges like the `@urql/exchange-persisted` for network caching with WPGraphQL SmartCache.
 */
export const client = new Client({
  url: graphqlApi,
  exchanges: [fetchExchange],
});

export const fetchAllPaginated = async (query, getData, getPageInfo) => {
  const allData = [];
  let hasNextPage = true;
  let after = null;

  while (hasNextPage) {
    const { data, error } = await client.query(query, { after });

    if (error) {
      console.error("Error fetching paginated data:", error);
      break;
    }

    allData.push(...getData(data));
    after = getPageInfo(data).endCursor;
    hasNextPage = getPageInfo(data).hasNextPage;
  }

  return allData;
};

export const authHeaders = (isPreview) => {
  return isPreview
    ? {
        Authorization: `Basic ${Buffer.from(
          `admin:cn5AhXDQx4rgDh5dqJQatsgE`
        ).toString("base64")}`,
      }
    : undefined;
};
