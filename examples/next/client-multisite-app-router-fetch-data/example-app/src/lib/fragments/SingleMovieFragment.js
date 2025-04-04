export const SingleMovieFragment = `
  fragment SingleMovieFragment on Movie {
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
