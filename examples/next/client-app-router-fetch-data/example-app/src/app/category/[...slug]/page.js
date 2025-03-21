import { PostListFragment } from "@/lib/fragments/PostListFragment";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

const CAT_POSTS_QUERY = `
  ${PostListFragment}
  query ListPosts($slug: String!, $after: String, $first: Int = 5) {
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

export default async function CategoryPage({ params }) {
  return BlogListingTemplate(CAT_POSTS_QUERY, params, "Category");
}
