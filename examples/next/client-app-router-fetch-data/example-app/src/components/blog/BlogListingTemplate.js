import BlogList from "@/components/blog/BlogList";
import { capitalizeWords } from "@/lib/utils";
import { getPosts, getPostsPerPage } from "@/lib/utils";
import { notFound } from "next/navigation";

// Note the approach here is to load the first 5 posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function BlogListingTemplate(query, params, titlePrefix) {
  // Get the last value in the array of params
  const slug = Array.isArray(params.slug)
    ? params.slug[params.slug.length - 1]
    : params.slug;

  // Fetch initial data on the server using the slug from the route
  const data = await getPosts({
    query: query,
    slug,
    pageSize: getPostsPerPage(),
    revalidate: 3600, // Caches for 60 minutes
  });

  // Check if posts exists then throw a 404
  if (!data || !data.posts || data.posts.edges.length === 0) {
    console.warn(`No posts found for ${titlePrefix.toLowerCase()}: ${slug}`);
    notFound();
  }

  const initialPosts = data.posts.edges;
  const initialPageInfo = data.posts.pageInfo;
  const capitalizeSlug = capitalizeWords(slug);

  return (
    <div className="container mx-auto px-4 pb-12" data-slug={slug}>
      <h1 className="text-3xl lg:text-4xl font-bold mb-8 container max-w-4xl text-center lg:text-left lg:px-10 py-2 mx-auto">
        {capitalizeSlug ? `${titlePrefix}: ${capitalizeSlug}` : titlePrefix}
      </h1>

      <BlogList
        initialPosts={initialPosts}
        initialPageInfo={initialPageInfo}
        postsPerPage={getPostsPerPage()}
        postsQuery={query}
        slug={slug}
      />
    </div>
  );
}
