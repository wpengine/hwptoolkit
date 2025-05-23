import Link from "next/link";

export default function Header() {
  // If you want to see how to fetch a blog title from one particular site,
  // check out the /examples/next/client-app-router-fetch-data/example-app/src/components/layout/Header.js file
  const blogTitle = "My Headless Sample Multisite";
  const menuItemClass = "text-lg hover:underline focus:underline";

  return (
    <header className="bg-gray-800 text-white py-4 px-8 mb-8">
      <div className="flex flex-col lg:flex-row justify-between items-center max-w-4xl mx-auto">
        <h1 className="text-3xl font-semibold w-full lg:w-auto mb-4 lg:mb-0 text-center lg:text-left">
          <Link href="/" className="hover:underline focus:underline">
            {blogTitle}
          </Link>
        </h1>

        <nav className="space-x-6 w-full lg:w-auto text-center lg:text-left">
          <Link href="/blog" className={menuItemClass}>
            Blog
          </Link>
          <Link href="/cinema-listings" className={menuItemClass}>
            Cinema Listings
          </Link>
          <Link href="/about-us" className={menuItemClass}>
            About Us
          </Link>
          <Link href="/bookings" className={menuItemClass}>
            Bookings
          </Link>
        </nav>
      </div>
    </header>
  );
}
