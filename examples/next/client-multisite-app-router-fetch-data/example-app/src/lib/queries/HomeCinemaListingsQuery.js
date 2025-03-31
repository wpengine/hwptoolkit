export const HomeCinemaListingsQuery = `
fragment MovieListFragment on Movie {
  id
  title
  uri
  featuredImage {
    node {
      sourceUrl
      altText
    }
  }
  movieShowTimes {
    daysOfTheWeek
    screenTimes
  }
}
query GetCinemaListings($after: String, $first: Int = 5) {
  movies(where: {status: PUBLISH}, after: $after, first: $first) {
    edges {
      node {
        ...MovieListFragment
      }
      cursor
    }
  }
}
`;
