
import { SinglePageFragment } from "@/lib/fragments/SinglePageFragment";
import { SinglePostFragment } from "@/lib/fragments/SinglePostFragment";
import { SingleMovieFragment } from "@/lib/fragments/SingleMovieFragment";


// Important Note: As not all sites have the same CPT, making a query for a CPT which doesn't exist for a site, will result in an error
// So instead we have an object with the key matching the siteKey defined in next.config.mjs

// Note: Also it probably isn't efficient to do a catch-all query for all post types. See - https://www.wpgraphql.com/docs/performance#performance-best-practices
// For this example we wanted to demonstrate how to use a catch all for different CPTs

// See WPGraphQL docs on nodeByUri: https://www.wpgraphql.com/2021/12/23/query-any-page-by-its-path-using-wpgraphql
export const NodeByUriQuery = {
    main: `
        ${SinglePageFragment}
        ${SinglePostFragment}
        query GetNodeByUri($uri: String!) {
            nodeByUri(uri: $uri) {
                __typename
                ...SinglePageFragment
                ...SinglePostFragment
            }
        }
    `,
    movie_site: `
    ${SinglePageFragment}
    ${SinglePostFragment}
    ${SingleMovieFragment}
    query GetNodeByUri($uri: String!) {
        nodeByUri(uri: $uri) {
            __typename
            ...SinglePageFragment
            ...SinglePostFragment
            ...SingleMovieFragment
        }
    }
`
};
