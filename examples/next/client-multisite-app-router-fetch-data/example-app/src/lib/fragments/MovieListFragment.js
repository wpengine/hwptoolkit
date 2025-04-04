export const MovieListFragment = `
fragment MovieListFragment on Movie {
  id
  title
  uri
  content
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
`;
