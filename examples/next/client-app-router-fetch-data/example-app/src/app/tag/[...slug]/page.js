// Define Query
import { PostListFragment } from '@/lib/fragments/PostListFragment';
import { BlogTemplate } from '@/components/blog/BlogTemplate';

const TAG_POSTS_QUERY = `
  ${PostListFragment}
  query ListPosts($slug: String!, $after: String, $first: Int = 5) {
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
    return BlogTemplate(TAG_POSTS_QUERY, params, 'Tag');
}
