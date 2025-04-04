import { MovieListFragment } from "@/lib/fragments/MovieListFragment";

export const CinemaListingsQuery = `
${MovieListFragment}
query GetCinemaListings($after: String, $first: Int = 5) {
  movies(where: {status: PUBLISH}, after: $after, first: $first) {
    edges {
      node {
        ...MovieListFragment
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
`;
