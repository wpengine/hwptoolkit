export const EventListFragment = `
fragment EventListFragment on Event {
    id
    title
    uri
    content
    location {
  	  edges {
  	    node {
  	     	name
  	    }
  	  }
  	}
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
