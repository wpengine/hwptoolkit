export const HomePostListQuery = `
  fragment PostListFragment on Post {
    id
    title
    uri
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
  query ListPosts($after: String, $first: Int = 5) {
    posts(after: $after, first: $first) {
      edges {
        node {
          ...PostListFragment
        }
      }
    }
  }
`;
