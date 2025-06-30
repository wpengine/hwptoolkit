/* eslint-disable @next/next/no-html-link-for-pages */
import { gql, useQuery } from "@apollo/client";
import Link from "next/link";

// Defining a GraphQL query to fetch the blog title
const GET_BLOG_TITLE = gql`
  query GetBlogTitle {
    allSettings {
      generalSettingsTitle
    }
  }
`;

export default function Header() {
  // Using the useQuery hook to execute the GraphQL query and get the data
  const { data } = useQuery(GET_BLOG_TITLE);
  // Extracting the blog title from the fetched data
  const blogTitle = data?.allSettings?.generalSettingsTitle;

  return (
    <header className='bg-gray-800 text-white py-4 px-8 mb-8'>
      <div className='flex justify-between items-center max-w-4xl mx-auto'>
        <div className='text-3xl font-semibold'>
          <Link href='/'>{blogTitle}</Link>
        </div>

        <nav className='space-x-6'>
          <Link href='/' className='text-lg hover:underline'>
            Home
          </Link>
        </nav>
      </div>
    </header>
  );
}
