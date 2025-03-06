import { gql, useQuery } from "@apollo/client";
import Link from "next/link";

const GET_BLOG_TITLE = gql`
  query GetBlogTitle {
    allSettings {
      generalSettingsTitle
    }
  }
`;

export default function Header() {
  const { data } = useQuery(GET_BLOG_TITLE);
  const blogTitle = data?.allSettings?.generalSettingsTitle;

  return (
    <header class='bg-gray-800 text-white py-4 px-8 mb-8'>
      <div class='flex justify-between items-center max-w-4xl mx-auto'>
        <h1 class='text-3xl font-semibold'>
          <Link href='/'>{blogTitle}</Link>
        </h1>

        <nav class='space-x-6'>
          <a href='/' class='text-lg hover:underline'>
            Home
          </a>
          <a href='/privacy-policy' class='text-lg hover:underline'>
            Privacy Policy
          </a>
        </nav>
      </div>
    </header>
  );
}
