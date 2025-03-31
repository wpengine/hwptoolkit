import { PostListQuery } from "@/lib/queries/PostListQuery";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

export default function BlogPage(params) {
  return BlogListingTemplate(PostListQuery, {
    "params": params,
    "siteKey": "main",
    "titlePrefix": "Blog",
    "cacheExpiry": 3600,
  });
}
