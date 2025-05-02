import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";
import Link from "next/link";

export default function CategoryIndexPage({ categories }) {
  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">Categories</h1>
      <div className="space-y-4">
        {categories.map((category) => (
          <div key={category.slug} className="py-2">
            <Link
              href={`${category.uri}`}
              className="text-xl font-medium text-indigo-600 hover:text-indigo-800"
            >
              {category.name}
            </Link>
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
      query GetCategories {
        categories {
          nodes {
            name
            slug
            uri
          }
        }
      }
    `,
  });

  return {
    props: {
      categories: data.categories.nodes,
    },
  };
}
