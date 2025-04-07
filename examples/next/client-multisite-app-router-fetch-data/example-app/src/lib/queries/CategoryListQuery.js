import { PostListFragment } from "@/lib/fragments/PostListFragment";
export const CategoryListQuery = `
  ${PostListFragment}
  query ListPostsForCategory($slug: String!, $after: String, $first: Int = 5) {
    posts(where: { categoryName: $slug }, after: $after, first: $first) {
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
