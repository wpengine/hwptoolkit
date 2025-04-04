import { getPosts, getPostsPerPage } from "@/lib/utils";
import { PageHeading } from "@/components/heading/PageHeading";
import { notFound } from "next/navigation";
import CustomPostTypeList from "./CustomPostTypeList";

// Note the approach here is to load the first 5 custom posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function CustomPostTypeTemplate(query, args) {
  const {
    siteKey,
    title,
    customPostType,
    postsPerPage,
    cacheExpiry,
    containerClass,
    postListContainerClass,
  } = args;

  // Fetch initial data on the server using the slug from the route
  const data = await getPosts({
    query: query,
    siteKey: siteKey,
    pageSize: postsPerPage ? postsPerPage : getPostsPerPage(),
    revalidate: cacheExpiry ? cacheExpiry : 3600,
  });

  // Check if posts exists then throw a 404
  if (
    !data ||
    !data[customPostType] ||
    data[customPostType].edges.length === 0
  ) {
    console.warn(`No posts found for the custom post type ${customPostType}`);
    notFound();
  }

  const initialPosts = data[customPostType].edges;
  const initialPageInfo = data[customPostType].pageInfo;

  return (
    <div
      className={containerClass || "container mx-auto px-4 pb-12"}
      data-cpt={customPostType}
    >
      {title && <PageHeading heading={title} />}
      <CustomPostTypeList
        initialPosts={initialPosts}
        initialPageInfo={initialPageInfo}
        postsPerPage={postsPerPage || getPostsPerPage()}
        postsQuery={query}
        siteKey={siteKey}
        customPostType={customPostType}
        postListContainerClass={postListContainerClass}
      />
    </div>
  );
}
