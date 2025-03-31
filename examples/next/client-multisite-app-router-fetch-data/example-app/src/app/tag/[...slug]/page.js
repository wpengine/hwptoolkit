import { TagListQuery } from "@/lib/queries/TagListQuery.js";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

export default function TagPage({params}) {
    return BlogListingTemplate(TagListQuery, {
      "params": params,
      "siteKey": "main",
      "titlePrefix": "Tag",
      "cacheExpiry": 3600,
    });
}
