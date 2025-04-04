import { PostListFragment } from "@/lib/fragments/PostListFragment";
export const TagListQuery = `
  ${PostListFragment}
  query ListPostsForTag($slug: String!, $after: String, $first: Int = 5) {
    posts(where: { tag: $slug }, after: $after, first: $first) {
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
