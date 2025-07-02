import { BlogPostItem } from "@/components/BlogPostItem";
import { fetchWP } from "@/lib/fetchWP";

export default async function Blog() {
  const posts = await fetchWP("posts");

  return posts.map((post) => <BlogPostItem key={post.id} post={post} />);
}
