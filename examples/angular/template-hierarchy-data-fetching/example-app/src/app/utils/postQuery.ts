import { gql } from './graphql.service';

export const POSTS_QUERY = gql`
  query GetPosts(
    $first: Int = 9
    $after: String
    $category: String
    $tag: String
  ) {
    posts(
      first: $first
      after: $after
      where: { categoryName: $category, tag: $tag }
    ) {
      pageInfo {
        hasNextPage
        endCursor
      }
      edges {
        cursor
        node {
          id
          title
          date
          excerpt
          uri
          slug
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
          categories {
            nodes {
              name
              slug
            }
          }
          tags {
            nodes {
              name
              slug
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
      }
    }
  }
`;
