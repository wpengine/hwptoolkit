import { PostListFragment } from "@/lib/fragments/PostListFragment";
import { BlogTemplate } from "@/components/blog/BlogTemplate";

const LIST_POSTS_QUERY = `
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

export default async function BlogPage(params) {
  return BlogTemplate(LIST_POSTS_QUERY, params, "Blog");
}
