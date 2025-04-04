import { HomePostListQuery } from "@/lib/queries/HomePostListQuery";
import { HomeCinemaListingsQuery } from "@/lib/queries/HomeCinemaListingsQuery";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";
import { CustomPostTypeTemplate } from "@/components/cpt/CustomPostTypeTemplate";
import { PageHeading } from "@/components/heading/PageHeading";
import { Button } from "@/components/button/Button";

export default async function HomePage(params) {
  const postListContainerClass =
    "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4";

  return (
    <>
      <div className="homepage-blog-listings my-20">
        <PageHeading
          heading="Latest News"
          className="text-3xl lg:text-4xl font-bold mb-4 container max-w-4xl text-center mx-auto"
        />

        {BlogListingTemplate(HomePostListQuery, {
          params: params,
          siteKey: "main",
          cacheExpiry: 86400,
          postsPerPage: 3,
          postListContainerClass: postListContainerClass,
        })}

        <div className="flex flex-col items-center">
          <Button text="View All News" href="/blog" />
        </div>
      </div>
      <div className="homepage-cinema-listings pb-20">
        <PageHeading
          heading="Latest Cinema Listings"
          className="text-3xl lg:text-4xl font-bold mb-4 container max-w-4xl text-center mx-auto"
        />
        {CustomPostTypeTemplate(HomeCinemaListingsQuery, {
          customPostType: "movies",
          siteKey: "movie_site",
          postsPerPage: 3,
          cacheExpiry: 86400,
          containerClass: "container mx-auto px-4 pb-12",
          postListContainerClass: postListContainerClass,
        })}
        <div className="flex flex-col items-center">
          <Button text="View All Cinema Listings" href="/cinema-listings" />
        </div>
      </div>
    </>
  );
}
