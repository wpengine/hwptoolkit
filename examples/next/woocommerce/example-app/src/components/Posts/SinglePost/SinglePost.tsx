import React, { useState } from "react";
import { useRouter } from "next/router";
import Image from "next/image";
import Link from "next/link";
import { Post, Category, Tag } from "@/interfaces/post.interface";
import { formatDate } from "@/lib/utils";

interface PostResponse {
  post: Post;
}

export default function SinglePost({ post }: PostResponse) {
  if (!post) {
    return null;
  }
  const router = useRouter();
  const [avatarError, setAvatarError] = useState<boolean>(false);

  // Helper functions
  const hasCategories = (): boolean => {
    return !!(post?.categories?.nodes && post.categories.nodes.length > 0);
  };

  const hasTags = (): boolean => {
    return !!(post?.tags?.nodes && post.tags.nodes.length > 0);
  };

  const hasFeaturedImage = (): boolean => {
    return !!post?.featuredImage?.node?.sourceUrl;
  };

  const hasAuthor = (): boolean => {
    return !!post?.author?.node;
  };

  const hasAuthorAvatar = (): boolean => {
    return !!post?.author?.node?.avatar?.url;
  };

  const getCategoryLink = (category: Category): string => {
    return category.slug
      ? `/category/${category.slug}`
      : `/category/${category.name.toLowerCase()}`;
  };

  const getTagLink = (tag: Tag): string => {
    return tag.slug ? `/tag/${tag.slug}` : `/tag/${tag.name.toLowerCase()}`;
  };

  const isLastItem = (index: number, array: any[]): boolean => {
    return index === array.length - 1;
  };

  // Post not found
  if (!post) {
    return (
      <div className="container mx-auto px-4 py-10">
        <div className="text-center bg-gray-50 border border-gray-200 rounded-lg p-8">
          <h2 className="text-xl font-bold text-gray-800 mb-2">
            Post not found
          </h2>
          <p className="text-gray-600">
            The post you're looking for doesn't exist.
          </p>
          <Link
            href="/blog"
            className="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            Back to Blog
          </Link>
        </div>
      </div>
    );
  }

  // Main post content
  if (post) {
    return (
      <div className="container mx-auto px-4 py-8">
        <article className="max-w-4xl mx-auto">
          {/* Post Header */}
          <header className="mb-8">
            <h1 className="text-4xl md:text-5xl font-bold text-gray-900 mb-6 leading-tight">
              {post.title}
            </h1>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-6 border-b border-gray-200">
              {hasAuthor() && (
                <div className="flex items-center gap-3">
                  {hasAuthorAvatar() && !avatarError ? (
                    <Image
                      src={post.author!.node.avatar!.url}
                      alt={post.author!.node.name}
                      width={48}
                      height={48}
                      className="w-12 h-12 rounded-full border-2 border-gray-200"
                      loading="lazy"
                      onError={() => setAvatarError(true)}
                    />
                  ) : (
                    <div className="w-12 h-12 bg-blue-500 text-white rounded-full flex items-center justify-center text-lg font-bold">
                      {post.author!.node.name.charAt(0).toUpperCase()}
                    </div>
                  )}
                  <span className="text-lg font-medium text-gray-700">
                    By {post.author!.node.name}
                  </span>
                </div>
              )}
              <time className="text-gray-500 text-sm" dateTime={post.date}>
                {formatDate(post.date)}
              </time>
            </div>
          </header>

          {/* Featured Image */}
          {hasFeaturedImage() && (
            <div className="mb-8 rounded-lg overflow-hidden shadow-lg">
              <Image
                src={post.featuredImage!.node.sourceUrl}
                alt={post.featuredImage!.node.altText || post.title}
                width={800}
                height={400}
                className="w-full h-auto object-cover"
                loading="lazy"
                placeholder="blur"
                blurDataURL="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k="
              />
            </div>
          )}

          {/* Post Content */}
          <div
            className="prose prose-lg prose-blue max-w-none mb-12 text-gray-800 leading-relaxed"
            dangerouslySetInnerHTML={{ __html: post.content }}
          />

          {/* Post Footer */}
          <footer className="border-t border-gray-200 pt-8">
            {hasCategories() && (
              <div className="mb-6">
                <span className="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3 block">
                  Categories:
                </span>
                <div className="flex flex-wrap gap-2">
                  {post.categories!.nodes.map((category, index) => (
                    <span
                      key={category.slug || category.name}
                      className="inline-flex items-center"
                    >
                      <Link
                        href={getCategoryLink(category)}
                        className="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium hover:bg-red-200 transition-colors"
                      >
                        {category.name}
                      </Link>
                      {!isLastItem(index, post.categories!.nodes) && (
                        <span className="ml-2 text-gray-400">,</span>
                      )}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {hasTags() && (
              <div className="mb-6">
                <h3 className="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">
                  Tags:
                </h3>
                <div className="flex flex-wrap gap-2">
                  {post.tags!.nodes.map((tag, index) => (
                    <span
                      key={tag.slug || tag.name}
                      className="inline-flex items-center"
                    >
                      <Link
                        href={getTagLink(tag)}
                        className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium hover:bg-blue-200 transition-colors"
                      >
                        #{tag.name}
                      </Link>
                      {!isLastItem(index, post.tags!.nodes) && (
                        <span className="ml-2 text-gray-400">,</span>
                      )}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </footer>

          {/* Comments Section - Placeholder */}
          {post.databaseId && (
            <section className="mt-12 pt-8 border-t border-gray-200">
              <h3 className="text-2xl font-bold text-gray-900 mb-6">
                Comments
              </h3>
              <div className="bg-gray-50 rounded-lg p-6 text-center">
                <p className="text-gray-600">
                  Comments section for post ID: {post.databaseId}
                </p>
                <p className="text-sm text-gray-500 mt-2">
                  Comment count: {post.commentCount || 0}
                </p>
              </div>
            </section>
          )}

          {/* Navigation */}
          <nav className="mt-12 pt-8 border-t border-gray-200">
            <Link
              href="/blog"
              className="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
            >
              ← Back to Blog
            </Link>
          </nav>
        </article>
      </div>
    );
  }
}
