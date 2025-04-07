import { CategoryListQuery } from "@/lib/queries/CategoryListQuery";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

// Note: This is only rendering posts for the main site.
// See catch all template to see how you could implement a multisite solution.
export default function CategoryPage({ params }) {
  return BlogListingTemplate(CategoryListQuery, {
    params: params,
    siteKey: "main",
    titlePrefix: "Category",
    cacheExpiry: 3600,
  });
}
