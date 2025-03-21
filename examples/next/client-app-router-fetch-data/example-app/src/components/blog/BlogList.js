"use client";

import { useState } from "react";
import { fetchGraphQL } from "@/lib/client";
import BlogPostItem from "@/components/blog/BlogPostItem";

export default function BlogList({
  initialPosts,
  initialPageInfo,
  postsPerPage,
  postsQuery,
  slug = "",
}) {
  // Track various states for posts, page info, and loading
  const [posts, setPosts] = useState(initialPosts || []);
  const [pageInfo, setPageInfo] = useState(initialPageInfo || {});
  const [loading, setLoading] = useState(false);

  const loadMorePosts = async () => {
    if (!pageInfo.hasNextPage) return;

    setLoading(true);
    try {
      let data;

      // Category or Tag
      if (slug) {
        data = await fetchGraphQL(postsQuery, {
          slug: slug,
          first: postsPerPage,
          after: pageInfo.endCursor,
        });
      } else {
        data = await fetchGraphQL(postsQuery, {
          first: postsPerPage,
          after: pageInfo.endCursor,
        });
      }
      const newPosts = data?.posts?.edges || [];
      const newPageInfo = data?.posts?.pageInfo || {};

      setPosts((prevPosts) => [...prevPosts, ...newPosts]);
      setPageInfo(newPageInfo);
    } catch (error) {
      console.error("Error loading more posts:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <div className="grid post-list gap-4">
        {posts.map(({ node }) => (
          <BlogPostItem key={node.id} post={node} />
        ))}
      </div>

      {pageInfo.hasNextPage && (
        <button
          onClick={loadMorePosts}
          type="button"
          className="px-8 py-3 font-semibold rounded bg-gray-800 hover:bg-gray-700 text-gray-100 mx-auto block mt-8"
          disabled={loading}
        >
          {loading ? "Loading..." : "Load more"}
        </button>
      )}
    </>
  );
}
