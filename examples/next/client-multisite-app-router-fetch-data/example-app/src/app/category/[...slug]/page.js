import { CategoryListQuery } from "@/lib/queries/CategoryListQuery";
import { BlogListingTemplate } from "@/components/blog/BlogListingTemplate";

export default function CategoryPage({params}) {
  return BlogListingTemplate(CategoryListQuery, params, "main", "Category");
}
