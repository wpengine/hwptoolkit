import { getPosts, getPostsPerPage } from "@/lib/utils";
import { Heading } from "@/components/heading/heading";
import { notFound } from "next/navigation";
import CustomPostTypeList from "./CustomPostTypeList";

// Note the approach here is to load the first 5 custom posts on the server,
// and then use the client-side component to handle pagination after hydrating the initial data.
export async function CustomPostTypeTemplate(query, siteKey, customPostType, title) {
  // Fetch initial data on the server using the slug from the route
  const data = await getPosts({
    query: query,
    siteKey: siteKey,
    pageSize: getPostsPerPage(),
    revalidate: 3600, // Caches for 60 minutes
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
    <div className="container mx-auto px-4 pb-12" data-cpt={customPostType}>
      <Heading heading={title} />

      <CustomPostTypeList
        initialPosts={initialPosts}
        initialPageInfo={initialPageInfo}
        postsPerPage={getPostsPerPage()}
        postsQuery={query}
        siteKey={siteKey}
        customPostType={customPostType}
      />
    </div>
  );
}
