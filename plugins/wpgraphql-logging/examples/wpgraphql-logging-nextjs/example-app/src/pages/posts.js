import { client } from "@/lib/client";
import { gql } from "@apollo/client";
import Head from "next/head";
import Link from "next/link";

const LIST_POSTS_WITH_PAGINATION = gql`
  query ListPostsWithPagination($first: Int = 10, $after: String) {
    posts(first: $first, after: $after) {
      pageInfo {
        hasNextPage
        hasPreviousPage
        endCursor
        startCursor
      }
      edges {
        cursor
        node {
          id
          title
          uri
          excerpt
          date
          author {
            node {
              name
            }
          }
          featuredImage {
            node {
              sourceUrl(size: MEDIUM)
              caption
            }
          }
        }
      }
    }
  }
`;

export default function Posts({ data }) {
  const posts = data?.posts?.edges || [];

  return (
    <>
      <Head>
        <title>All Posts</title>
      </Head>

      <div className="max-w-6xl mx-auto px-4 py-8">
        <h1 className="text-4xl font-bold mb-8">All Posts</h1>

        <div className="grid md:grid-cols-2 gap-6">
          {posts.map(({ node: post }) => (
            <Link
              key={post.id}
              href={post.uri}
              className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
            >
              {post.featuredImage && (
                <img
                  src={post.featuredImage.node.sourceUrl}
                  alt={post.title}
                  className="w-full h-48 object-cover"
                />
              )}
              <div className="p-6">
                <h2 className="text-2xl font-bold mb-2">{post.title}</h2>
                <p className="text-gray-600 text-sm mb-2">
                  By {post.author.node.name} on{" "}
                  {new Date(post.date).toLocaleDateString()}
                </p>
                <div
                  className="text-gray-700"
                  dangerouslySetInnerHTML={{ __html: post.excerpt }}
                />
              </div>
            </Link>
          ))}
        </div>
      </div>
    </>
  );
}

export async function getServerSideProps() {
  try {
    const { data } = await client.query({
      query: LIST_POSTS_WITH_PAGINATION,
      variables: { first: 10 },
    });

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

