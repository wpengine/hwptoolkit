import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";
import Link from "next/link";

export default function Post({ post }) {
  if (!post) {
    return <div>Post not found!</div>;
  }

  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">
        {post.title}
      </h1>
      <div className="text-gray-600 text-sm mb-4">
        <span>Published on: {new Date(post.date).toLocaleDateString()}</span>
      </div>

      <div
        className="post-content text-gray-700 mb-8"
        dangerouslySetInnerHTML={{ __html: post.content }}
      />

      <div className="post-excerpt mb-6">
        <h3 className="text-2xl font-semibold text-gray-800">Excerpt</h3>
        <div
          className="text-gray-600"
          dangerouslySetInnerHTML={{ __html: post.excerpt }}
        />
      </div>

      <Link href="/posts" className="text-indigo-600 hover:text-indigo-800">
          Back to All Posts
        </Link>
    </div>
  );
}

export async function getStaticProps({ params }) {
  const client = getApolloClient();
  const { slug } = params;

  const { data } = await client.query({
    query: gql`
      query GetPostById($id: ID!) {
        post(id: $id, idType: SLUG) {
          title
          content
          excerpt
        }
      }
    `,
    variables: { id: slug },
  });
  return {
    props: {
      post: data.post,
    },
    revalidate: 10,
  };
}

export async function getStaticPaths() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetAllPostSlugs {
        posts {
          nodes {
            slug
          }
        }
      }
    `,
  });

  // Generate paths for each post using the slug
  const paths = data.posts.nodes.map((post) => ({
    params: { slug: post.slug },
  }));

  return {
    paths,
    fallback: "blocking",
  };
}
