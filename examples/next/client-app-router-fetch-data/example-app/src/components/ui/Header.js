import Link from "next/link";


async function fetchBlogTitle() {
    const query = `
        query GetBlogTitle {
            allSettings {
                generalSettingsTitle
            }
        }
    `;

    const encodedQuery = encodeURIComponent(query);
    const response = await fetch(
        `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql?query=${encodedQuery}`,
        {
            // Used GET for caching purposes
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            // Use Next Caching for ISR
            // More info: https://nextjs.org/docs/app/building-your-application/data-fetching/incremental-static-regeneration
            next: {
                revalidate: 86400,
            },
        }
    );

    const { data } = response.json();
    return data?.allSettings?.generalSettingsTitle || 'My Example Site';
}

export default function Header() {

    const blogTitle = fetchBlogTitle();
    const menuItemClass = 'text-lg hover:underline';

    return (
        // @TODO Make the menu more friendly
        <header className='bg-gray-800 text-white py-4 px-8 mb-8'>
            <div className='flex justify-between items-center max-w-4xl mx-auto'>
                <h1 className='text-3xl font-semibold'>
                    <Link href='/'>{blogTitle}</Link>
                </h1>

                {/* 
                Note: Currently the default theme of Twenty Twenty Five does not have menu locations
                Therefore you cannot query menus out of the box with WPGraphQL. See - https://www.wpgraphql.com/docs/menus
                */}

                <nav className='space-x-6'>
                    <Link href='/' className={menuItemClass}>
                        Home
                    </Link>
                    <Link href='/blog' className={menuItemClass}>
                        Blog
                    </Link>
                    <Link href='/events' className={menuItemClass}>
                        Events
                    </Link>
                    <Link href='/about-us' className={menuItemClass}>
                        About
                    </Link>
                </nav>
            </div>
        </header>
    );
}
