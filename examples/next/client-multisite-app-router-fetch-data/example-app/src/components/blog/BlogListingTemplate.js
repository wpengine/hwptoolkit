import BlogList from "@/components/blog/BlogList";
import { PageHeading } from "@/components/heading/PageHeading";
import { getPosts, getPostsPerPage } from "@/lib/utils";
import { notFound } from "next/navigation";

// Note the approach here is to load the first 5 posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function BlogListingTemplate(query, args) {
  const {
    params,
    siteKey,
    titlePrefix,
    postsPerPage,
    cacheExpiry,
    containerClass,
    postListContainerClass,
  } = args;

  // Get the last value in the array of params
  const slug = Array.isArray(params.slug)
    ? params.slug[params.slug.length - 1]
    : params.slug;

  // Fetch initial data on the server using the slug from the route
  const data = await getPosts({
    query,
    siteKey,
    slug,
    pageSize: postsPerPage ? postsPerPage : getPostsPerPage(),
    revalidate: cacheExpiry ? cacheExpiry : 3600,
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
    <div
      className={containerClass || "container mx-auto px-4 pb-12"}
      data-slug={slug}
    >
      {title && <PageHeading heading={title} />}

      <BlogList
        initialPosts={initialPosts}
        initialPageInfo={initialPageInfo}
        postsPerPage={getPostsPerPage()}
        postsQuery={query}
        siteKey={siteKey}
        slug={slug}
        postListContainerClass={postListContainerClass}
      />
    </div>
  );
}
