import { TagListQuery } from "@/lib/queries/TagListQuery.js";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

// Note: This is only rendering posts for the main site.
// See catch all template to see how you could implement a multisite solution.
export default function TagPage({ params }) {
  return BlogListingTemplate(TagListQuery, {
    params: params,
    siteKey: "main",
    titlePrefix: "Tag",
    cacheExpiry: 3600,
  });
}
