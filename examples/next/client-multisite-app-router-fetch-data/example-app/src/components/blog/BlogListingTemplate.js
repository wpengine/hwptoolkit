import BlogList from "@/components/blog/BlogList";
import { Heading } from "@/components/heading/heading";
import { getPosts, getPostsPerPage } from "@/lib/utils";
import { notFound } from "next/navigation";

// Note the approach here is to load the first 5 posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function BlogListingTemplate(query, params, siteKey, titlePrefix) {
  // Get the last value in the array of params
  const slug = Array.isArray(params.slug)
    ? params.slug[params.slug.length - 1]
    : params.slug;

  // Fetch initial data on the server using the slug from the route
  const data = await getPosts({
    query,
    siteKey,
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

  let title = titlePrefix;
  if (slug) {
    title = `${titlePrefix}: ${slug}`;
  }

  return (
    <div className="container mx-auto px-4 pb-12" data-slug={slug}>
      <Heading heading={title} />

      <BlogList
        initialPosts={initialPosts}
        initialPageInfo={initialPageInfo}
        postsPerPage={getPostsPerPage()}
        postsQuery={query}
        siteKey={siteKey}
        slug={slug}
      />
    </div>
  );
}
