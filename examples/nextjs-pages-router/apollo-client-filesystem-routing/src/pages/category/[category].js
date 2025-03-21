import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";

export default function CategoryPage({ posts, categoryName }) {
  if (!posts.length) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="text-center text-xl text-gray-700">
          <h2>No posts found for category: {categoryName}</h2>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-gray-50 py-12 min-h-screen">
      <div className="container mx-auto px-6">
        <h1 className="text-3xl font-bold text-center text-gray-800 mb-8">
          Posts in Category:{" "}
          <span className="text-indigo-600">{categoryName}</span>
        </h1>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {posts.map((post) => (
            <div
              key={post.uri}
              className="bg-white rounded-lg shadow-lg overflow-hidden"
            >
              <div className="p-6">
                <h2 className="text-xl font-semibold text-gray-800 mb-4">
                  {post.title}
                </h2>
                <div
                  className="text-gray-600 text-sm"
                  dangerouslySetInnerHTML={{ __html: post.excerpt }}
                />
                <div className="mt-4">
                  <a
                    href={post.uri}
                    className="text-indigo-600 hover:text-indigo-800 text-sm font-medium"
                  >
                    Read more
                  </a>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export async function getStaticProps({ params }) {
  const { category } = params;
  const client = getApolloClient();

  const { data } = await client.query({
    query: gql`
      query GetPostsByCategory($categoryName: String!) {
        posts(where: { categoryName: $categoryName }) {
          nodes {
            title
            uri
            excerpt
          }
        }
      }
    `,
    variables: { categoryName: category },
  });

  return { props: { posts: data.posts.nodes, categoryName: category } };
}

export async function getStaticPaths() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetCategories {
        categories {
          nodes {
            slug
          }
        }
      }
    `,
  });

  const paths = data.categories.nodes.map((category) => ({
    params: { category: category.slug },
  }));

  return {
    paths,
    fallback: "blocking",
  };
}
