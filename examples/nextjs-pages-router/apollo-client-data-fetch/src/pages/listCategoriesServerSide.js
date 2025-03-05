import { client } from "@/lib/client";
import { gql } from "@apollo/client";
import Link from "next/link";

const LIST_CATEGORIES = gql`
  query ListCategoriesQuery {
    categories {
      edges {
        node {
          id
          name
          uri
        }
      }
    }
  }
`;

export default function ListCategoriesServerSide({ data }) {
  const categories = data?.categories;

  return (
    <aside className='max-w-2xl px-6 py-24 mx-auto space-y-12'>
      <nav className='w-full mx-auto space-y-4 text-center'>
        <h2 className='text-2xl font-semibold tracking-wide uppercase text-gray-800'>Categories</h2>
        <div className='flex flex-col space-y-2 text-lg text-gray-800'>
          {categories.edges.map(({ node: cat }) => (
            <Link key={cat.url} rel='noopener noreferrer' href={cat.uri} className='text-orange-600 hover:underline'>
              {cat.name}
            </Link>
          ))}
        </div>
      </nav>
    </aside>
  );
}

export async function getServerSideProps() {
  const { data } = await client.query({ query: LIST_CATEGORIES });

  return {
    props: {
      data,
    },
  };
}
