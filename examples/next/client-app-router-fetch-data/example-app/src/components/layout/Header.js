import Link from "next/link";

async function fetchBlogTitle() {
  const query = `
        query GetBlogTitle {
            allSettings {
                generalSettingsTitle
            }
        }
    `;

  const response = await fetch(
    `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        query,
      }),
      // Use Next Caching for ISR
      // More info: https://nextjs.org/docs/app/building-your-application/data-fetching/incremental-static-regeneration
      next: {
        revalidate: 86400,
      },
    },
  );

  if (!response.ok) {
    const errorText = await response.text();
    console.error("GraphQL Error Response:", errorText);
    throw new Error(
      `Failed to fetch: ${response.status} ${response.statusText}`,
    );
  }

  const { data } = await response.json();
  return data?.allSettings?.generalSettingsTitle || "Placeholder Title";
}

export default function Header() {
  const blogTitle = fetchBlogTitle();
  const menuItemClass = "text-lg hover:underline focus:underline";

  return (
    <header className="bg-gray-800 text-white py-4 px-8 mb-8">
      <div className="flex flex-col md:flex-row justify-between items-center max-w-4xl mx-auto">
        <h1 className="text-3xl font-semibold w-full md:w-auto mb-4 md:mb-0 text-center md:text-left">
          <Link href="/" className="hover:underline focus:underline">
            {blogTitle}
          </Link>
        </h1>

        <nav className="space-x-6 w-full md:w-auto text-center md:text-left">
          <Link href="/blog" className={menuItemClass}>
            Blog
          </Link>
          <Link href="/events" className={menuItemClass}>
            Events
          </Link>
          <Link href="/about-us" className={menuItemClass}>
            About
          </Link>
        </nav>
      </div>
    </header>
  );
}
