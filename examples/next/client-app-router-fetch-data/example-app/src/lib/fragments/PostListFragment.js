export const PostListFragment = `
  fragment PostListFragment on Post {
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
