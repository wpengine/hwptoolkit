import { PostListFragment } from "@/lib/fragments/PostListFragment";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

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
  return BlogListingTemplate(LIST_POSTS_QUERY, params, "main", "Blog");
}
