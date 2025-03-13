import { PostFragment } from '@/lib/fragments/PostFragment';
import { BlogTemplate } from '@/components/blog/BlogTemplate';

const CAT_POSTS_QUERY = `
  ${PostFragment}
  query ListPosts($slug: String!, $after: String, $first: Int = 5) {
    posts(where: { categoryName: $slug }, after: $after, first: $first) {
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

export default async function CategoryPage({ params }) {
    return BlogTemplate(CAT_POSTS_QUERY, params, 'Category');
}
