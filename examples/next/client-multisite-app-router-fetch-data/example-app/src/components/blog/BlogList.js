"use client";

import { useState } from "react";
import { fetchGraphQL } from "@/lib/client";
import BlogListItem from "@/components/blog/BlogListItem";
import { LoadMoreButton } from "../button/LoadMoreButton";

export default function BlogList({
  initialPosts,
  initialPageInfo,
  postsPerPage,
  postsQuery,
  siteKey,
  slug = "",
  postListContainerClass,
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

      /**
       * if a category or tag slug is provided
       */
      if (slug) {
        data = await fetchGraphQL(postsQuery, siteKey, {
          slug: slug,
          first: postsPerPage,
          after: pageInfo.endCursor,
        });
      } else {
        data = await fetchGraphQL(postsQuery, siteKey, {
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
      <div className={postListContainerClass || "grid post-list gap-4"}>
        {posts.map(({ node }) => (
          <BlogListItem key={node.id} post={node} />
        ))}
      </div>

      {pageInfo.hasNextPage && (
        <LoadMoreButton
          text="Load More Posts"
          onClick={loadMorePosts}
          loading={loading}
        />
      )}
    </>
  );
}
