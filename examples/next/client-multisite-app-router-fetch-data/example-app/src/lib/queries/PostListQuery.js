import { PostListFragment } from "@/lib/fragments/PostListFragment";
export const PostListQuery = `
  ${PostListFragment}
  query ListPosts($after: String, $first: Int = 5) {
    posts(after: $after, first: $first) {
      edges {
        node {
          ...PostListFragment
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;
