import { PostListFragment } from "@/lib/fragments/PostListFragment";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

const TAG_POSTS_QUERY = `
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

export default async function TagPage({ params }) {
  return BlogListingTemplate(TAG_POSTS_QUERY, params, "Tag");
}
