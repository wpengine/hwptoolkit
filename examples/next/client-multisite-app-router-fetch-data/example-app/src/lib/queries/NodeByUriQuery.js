import { SinglePageFragment } from "@/lib/fragments/SinglePageFragment";
import { SinglePostFragment } from "@/lib/fragments/SinglePostFragment";

// See WPGraphQL docs on nodeByUri: https://www.wpgraphql.com/2021/12/23/query-any-page-by-its-path-using-wpgraphql
// Also add other Fragments for other post types here too
export const NodeByUriQuery = `
  ${SinglePageFragment}
  ${SinglePostFragment}
  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...SinglePageFragment
      ...SinglePostFragment
    }
  }
`;
