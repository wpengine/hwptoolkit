import { BlogPostItem } from "@/components/BlogPostItem";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";
import Head from "next/head";

const LIST_POSTS = gql`
  query ListPosts {
    posts(first: 5) {
      edges {
        node {
          id
          title
          uri
          excerpt
          date
          featuredImage {
            node {
              sourceUrl(size: LARGE)
              caption
            }
          }
        }
      }
    }
  }
`;

export default function Home({ data }) {
  const posts = data?.posts?.edges || [];

  return (
    <>
      <Head>
        <title>WPGraphQL Logging Example</title>
        <meta
          name="description"
          content="A simple Next.js app demonstrating WPGraphQL Logging"
        />
      </Head>

      <div className="max-w-4xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-4">
            Welcome to WPGraphQL Logging Example
          </h1>
          <p className="text-lg text-gray-600 mb-4">
            This example demonstrates the WPGraphQL Logging plugin. As you
            navigate through the site, GraphQL queries are being logged to the
            WordPress database.
          </p>
          <div className="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <p className="text-blue-700">
              <strong>View the logs:</strong> Login to WordPress admin and
              navigate to <strong>GraphQL Logs → All Logs</strong> to see the
              logged queries from this application.
            </p>
          </div>
        </div>

        <h2 className="text-2xl font-bold mb-6">Recent Posts</h2>

        {posts.length === 0 ? (
          <p className="text-gray-600">No posts found.</p>
        ) : (
          posts.map((item) => {
            const post = item.node;
            return <BlogPostItem key={post.id} post={post} />;
          })
        )}
      </div>
    </>
  );
}

// Fetch the initial list of posts at request time using getServerSideProps
export async function getServerSideProps() {
  try {
    const { data } = await client.query({ query: LIST_POSTS });

    return {
      props: {
        data,
      },
    };
  } catch (error) {
    console.error("Error fetching posts:", error);
    return {
      props: {
        data: { posts: { edges: [] } },
      },
    };
  }
}
