export const EventListFragment = `
fragment EventListFragment on Event {
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
    author {
      node {
        name
        avatar {
          url
        }
      }
    }
    eventFields {
      date
      startTime
      endTime
    }
  }
`;
