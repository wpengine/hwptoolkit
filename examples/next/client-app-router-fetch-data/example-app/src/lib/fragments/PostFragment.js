export const PostFragment = `
  fragment PostFragment on Post {
    id
    title
    uri
    excerpt
    date
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
  }
`;
