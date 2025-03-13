import BlogList from '@/components/blog/BlogList';
import { fetchGraphQL } from '@/lib/client';
import { PostFragment } from '@/lib/fragments/PostFragment';

// Posts per page. Please change if you want a different number of posts per page.
const POSTS_PER_PAGE = 5;

const LIST_POSTS_QUERY = `
  ${PostFragment}
  query ListPosts($after: String, $first: Int = 10) {
    posts(after: $after, first: $first) {
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

async function getPosts({ pageSize = POSTS_PER_PAGE, after = null }) {
  const data = await fetchGraphQL(LIST_POSTS_QUERY, {
    first: pageSize,
    after
  });

  return data;
}


// Note the approach here is to load the first 5 posts on the server, and then use the client-side component to handle pagination after hydrating the initial data.

export default async function BlogPage() {
  // Fetch initial data on the server
  const data = await getPosts({ pageSize: POSTS_PER_PAGE });
  const initialPosts = data.posts.edges;
  const initialPageInfo = data.posts.pageInfo;

  return (
    <div className="container mx-auto px-4 py-12">
      <h1 className="text-4xl font-bold mb-8">Blog</h1>

      <BlogList 
        initialPosts={initialPosts} 
        initialPageInfo={initialPageInfo} 
        postsPerPage={POSTS_PER_PAGE}
        postsQuery={LIST_POSTS_QUERY}
      />
    </div>
  );
}
