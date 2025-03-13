import { getPosts, getPostsPerPage } from '@/lib/utils';
import { notFound } from 'next/navigation';
import CustomPostTypeList from './CustomPostTypeList';

// Note the approach here is to load the first 5 posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function CustomPostTypeTemplate(query, customPostType, title) {

    // Fetch initial data on the server using the slug from the route
    const data = await getPosts({
        query: query,
        pageSize: getPostsPerPage()
    });


    // Check if posts exists then throw a 404
    if (!data || !data[customPostType] || data[customPostType].edges.length === 0) {
        console.warn(`No posts found for the custom post type ${customPostType}`);
        notFound();
    }

    const initialPosts = data[customPostType].edges;
    const initialPageInfo = data[customPostType].pageInfo;

    return (
        <div className="container mx-auto px-4 py-12" data-cpt={customPostType}>
            <h1 className="text-4xl font-bold mb-8 container max-w-4xl px-10 py-6 mx-auto">
                {title}
            </h1>

            <CustomPostTypeList
                initialPosts={initialPosts}
                initialPageInfo={initialPageInfo}
                postsPerPage={getPostsPerPage()}
                postsQuery={query}
                customPostType={customPostType}
            />
        </div>
    );
}
