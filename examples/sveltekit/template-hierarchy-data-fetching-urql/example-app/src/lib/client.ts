import {
  Client,
  fetchExchange,
  cacheExchange,
  ssrExchange,
  type OperationResult,
  type TypedDocumentNode,
} from "@urql/core";
// import { persistedExchange } from "@urql/exchange-persisted";
import merge from "deepmerge";
import { browser } from "$app/environment";
export { gql } from "@urql/core";

const GRAPHQL_URL = "index.php?graphql";
const graphqlApi = new URL(GRAPHQL_URL, "http://localhost:8888").href;

export const ssr = ssrExchange({
  isClient: browser,
});

/**
 * This is a very basic URQL client setup.
 * It uses the fetchExchange to make network requests.
 *
 * You can add more exchanges like the `@urql/exchange-persisted` for network caching with WPGraphQL SmartCache.
 */
export const client = new Client({
  url: graphqlApi,
  exchanges: [
    cacheExchange,
    ssr,
    // persistedExchange({ preferGetForPersistedQueries: true }),
    fetchExchange,
  ],
});

export type Paginator = (responseData: any) => {
  endCursor: string;
  hasNextPage: boolean;
};

export const fetchAllPaginated = async (
  fetch: typeof globalThis.fetch,
  query: TypedDocumentNode,
  getPageInfo: Paginator
) => {
  let allData = {};
  let lastResponse: OperationResult;
  let hasNextPage = true;
  let after = null;

  while (hasNextPage) {
    const resp: OperationResult = await client
      .query(query, { after }, { fetch })
      .toPromise();
    const { data, error } = resp;
    lastResponse = resp;

    if (error) {
      console.error("Error fetching paginated data:", error);
      break;
    }

    allData = merge(allData, data);

    after = getPageInfo(data).endCursor;
    hasNextPage = getPageInfo(data).hasNextPage;
  }

  lastResponse.data = allData;

  return lastResponse;
};
