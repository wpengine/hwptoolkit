import React from "react";
import Link from "next/link";
import Image from "next/image";
import { useState } from "react";
import { formatDate } from "@/lib/utils";
import { Post } from "@/interfaces/post.interface";
import styles from "@/components/Posts/PostCard.module.scss";

interface PostCardProps {
  post: Post;
  cols?: number;
}

export default function PostCard({ post, cols = 3 }: PostCardProps) {
  const [avatarError, setAvatarError] = useState<boolean>(false);

  if (!post) {
    return null;
  }

  const postImage: string =
    post.featuredImage?.node?.sourceUrl || "/placeholder-blog.jpg";
  const postAlt: string =
    post.featuredImage?.node?.altText || post.title || "Blog post image";
  const postDate: string = formatDate(post.date);

  const authorName: string = post.author?.node?.name || "Anonymous";
  const avatarUrl: string | undefined = post.author?.node?.avatar?.url;

  const categories = post.categories?.nodes || [];
  const tags = post.tags?.nodes || [];

  // Helper functions
  const hasCategories = (post: Post): boolean => {
    return !!(post.categories?.nodes && post.categories.nodes.length > 0);
  };

  const hasTags = (post: Post): boolean => {
    return !!(post.tags?.nodes && post.tags.nodes.length > 0);
  };

  const isLastItem = (index: number, array: any[]): boolean => {
    return index === array.length - 1;
  };

  const getCategoryLink = (slug: string): string => {
    return `/category/${slug}`;
  };

  const getTagLink = (slug: string): string => {
    return `/tag/${slug}`;
  };

  const createExcerpt = (excerpt: string, limit: number): string => {
    if (!excerpt) return "";
    const plainText = excerpt.replace(/<[^>]*>/g, "");
    return plainText.length > limit
      ? plainText.substring(0, limit) + "..."
      : plainText;
  };

  // Adjust excerpt length based on column count
  const getExcerptLength = (): number => {
    switch (cols) {
      case 1:
        return 250;
      case 2:
        return 180;
      case 3:
        return 150;
      case 4:
        return 120;
      case 5:
        return 100;
      case 6:
        return 80;
      default:
        return 150;
    }
  };

  // Get responsive classes based on column count
  const getTitleClass = () => {
    if (cols === 1) return "text-xl font-bold mb-3";
    if (cols >= 4) return "text-base font-bold mb-2 line-clamp-2";
    return "text-lg font-bold mb-2";
  };

  const getExcerptClass = () => {
    if (cols === 1) return "text-base text-gray-600 mb-4 leading-relaxed";
    if (cols >= 4) return "text-sm text-gray-600 mb-3 line-clamp-3";
    return "text-sm text-gray-600 mb-3";
  };

  const getMetaClass = () => {
    if (cols >= 4) return "flex flex-col gap-1 mb-2";
    return "flex justify-between items-center mb-2";
  };

  return (
    <article
      className={`${styles["post-card"]} bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300`}
    >
      {/* Featured image */}
      {post.featuredImage?.node && (
        <div className="relative overflow-hidden">
          <Link href={`/blog/${post.slug || post.uri}`}>
            <Image
              src={post.featuredImage.node.sourceUrl}
              alt={post.featuredImage.node.altText || post.title}
              width={400}
              height={cols === 1 ? 300 : cols >= 4 ? 160 : 200}
              className="w-full h-48 object-cover hover:scale-105 transition-transform duration-300"
              loading="lazy"
              placeholder="blur"
              blurDataURL="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k="
            />
          </Link>
        </div>
      )}

      <div className="p-4">
        <header className="mb-4">
          <h3 className={getTitleClass()}>
            <Link
              href={`/blog/${post.slug || post.uri}`}
              className="text-gray-800 hover:text-blue-600 transition-colors"
            >
              {post.title}
            </Link>
          </h3>

          <div>
            <div className={getMetaClass()}>
              {post.author?.node && (
                <div className="flex items-center gap-2">
                  {post.author.node.avatar?.url && !avatarError ? (
                    <Image
                      src={post.author.node.avatar.url}
                      alt={post.author.node.name}
                      width={24}
                      height={24}
                      className="w-6 h-6 rounded-full"
                      loading="lazy"
                      onError={() => setAvatarError(true)}
                    />
                  ) : (
                    <div className="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                      {authorName.charAt(0).toUpperCase()}
                    </div>
                  )}
                  <span className="text-sm text-blue-600 font-medium">
                    {post.author.node.name}
                  </span>
                </div>
              )}
              <time className="text-xs text-gray-500" dateTime={post.date}>
                {postDate}
              </time>
            </div>

            {/* Show categories/tags only for 1-3 columns */}
            {cols <= 3 && hasCategories(post) && (
              <div className="flex flex-wrap items-center gap-1 mb-2">
                <span className="text-xs font-semibold text-gray-600">
                  {post.categories!.nodes.length === 1
                    ? "Category:"
                    : "Categories:"}
                </span>
                <div className="flex flex-wrap gap-1">
                  {post
                    .categories!.nodes.slice(0, cols === 1 ? 5 : 2)
                    .map((category, index) => (
                      <span key={category.slug} className="text-xs">
                        <Link
                          href={getCategoryLink(category.slug)}
                          className="text-red-600 hover:text-red-800 hover:underline"
                        >
                          {category.name}
                        </Link>
                        {!isLastItem(
                          index,
                          post.categories!.nodes.slice(0, cols === 1 ? 5 : 2)
                        ) && <span className="text-gray-400">, </span>}
                      </span>
                    ))}
                </div>
              </div>
            )}

            {/* Show tags only for 1-2 columns */}
            {cols <= 2 && hasTags(post) && (
              <div className="flex flex-wrap items-center gap-1 mb-2">
                <span className="text-xs font-semibold text-gray-600">
                  {post.tags!.nodes.length === 1 ? "Tag:" : "Tags:"}
                </span>
                <div className="flex flex-wrap gap-1">
                  {post
                    .tags!.nodes.slice(0, cols === 1 ? 5 : 3)
                    .map((tag, index) => (
                      <span key={tag.slug} className="text-xs">
                        <Link
                          href={getTagLink(tag.slug)}
                          className="text-blue-600 hover:text-blue-800 hover:underline"
                        >
                          {tag.name}
                        </Link>
                        {!isLastItem(
                          index,
                          post.tags!.nodes.slice(0, cols === 1 ? 5 : 3)
                        ) && <span className="text-gray-400">, </span>}
                      </span>
                    ))}
                </div>
              </div>
            )}
          </div>
        </header>

        <div
          className={getExcerptClass()}
          dangerouslySetInnerHTML={{
            __html: createExcerpt(post.excerpt || "", getExcerptLength()),
          }}
        />

        <div className="mt-auto">
          <Link
            href={`${post.slug || post.uri}`}
            className="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors"
          >
            {cols >= 4 ? "Read →" : "Read more →"}
          </Link>
        </div>
      </div>
    </article>
  );
}
