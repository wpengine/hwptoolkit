'use client';

import { gql, useQuery } from '@apollo/client';
import QueryDebugger from '@/components/QueryDebugger';

const GET_POSTS = gql`
  query GetPosts {
    posts {
      nodes {
        id
        title
      }
    }
  }
`;

export default function Posts() {
  const { loading, error, data } = useQuery(GET_POSTS, {fetchPolicy: "no-cache"});

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  // Read complexity from response extensions if present
  const complexity =
    (data?.extensions && data.extensions().queryComplexity?.value) ?? null;
  return (
    <div>
      <QueryDebugger query={GET_POSTS} complexity={complexity} />

      <h2>Posts:</h2>
      <ul>
        {data.posts.nodes.map((post: any) => (
          <li key={post.id}>{post.title}</li>
        ))}
      </ul>
    </div>
  );
}