import { TagListQuery } from "@/lib/queries/TagListQuery.js";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

export default function TagPage({params}) {
  return BlogListingTemplate(TagListQuery, params, "main", "Tag");
}
