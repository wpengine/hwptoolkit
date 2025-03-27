import Link from "next/link";
import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";

export default function PostsPage({ posts }) {
  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">All Posts</h1>
      <div className="space-y-6">
        {posts.map((post) => (
          <div key={post.slug} className="bg-white p-6 rounded-lg shadow-lg">
            <h2 className="text-2xl font-semibold text-indigo-600 mb-4">
              <Link href={`/posts/${post.slug}`}>{post.title}</Link>
            </h2>
            <div
              className="text-gray-700 mb-4"
              dangerouslySetInnerHTML={{ __html: post.excerpt }}
            />
          </div>
        ))}
      </div>
    </div>
  );
}

export async function getStaticProps() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetPosts {
        posts {
          nodes {
            title
            slug
            excerpt
          }
        }
      }
    `,
  });

  return {
    props: {
      posts: data.posts.nodes,
    },
    revalidate: 10,
  };
}
