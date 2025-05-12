import { gql, useQuery } from '@apollo/client';

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
  const { loading, error, data } = useQuery(GET_POSTS);

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return (
    <ul>
      {data.posts.nodes.map((post: any) => (
        <li key={post.id}>{post.title}</li>
      ))}
    </ul>
  );
}