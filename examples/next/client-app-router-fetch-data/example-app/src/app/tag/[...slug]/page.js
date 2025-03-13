// Define Query
import { PostFragment } from '@/lib/fragments/PostFragment';
import { BlogTemplate } from '@/components/blog/BlogTemplate';

const TAG_POSTS_QUERY = `
  ${PostFragment}
  query ListPosts($slug: String!, $after: String, $first: Int = 5) {
    posts(where: { tag: $slug }, after: $after, first: $first) {
      edges {
        node {
          ...PostFragment
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
