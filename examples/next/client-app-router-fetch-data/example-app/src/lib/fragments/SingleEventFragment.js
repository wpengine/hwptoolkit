export const SingleEventFragment = `
fragment SingleEventFragment on Event {
    id
    title
    content
    uri
    eventFields {
      date
      startTime
      endTime
    }
    featuredImage {
      node {
        sourceUrl
        altText
      }
    }
    location {
      edges {
        node {
          name
        }
      }
    }
  }
`;
