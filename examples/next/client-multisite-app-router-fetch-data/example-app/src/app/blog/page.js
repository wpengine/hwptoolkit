import { PostListQuery } from "@/lib/queries/PostListQuery";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

// Note: This is only rendering posts for the main site.
// See catch all template to see how you could implement a multisite solution.
export default function BlogPage(params) {
  return BlogListingTemplate(PostListQuery, {
    params: params,
    siteKey: "main",
    titlePrefix: "Blog",
    cacheExpiry: 3600,
  });
}
