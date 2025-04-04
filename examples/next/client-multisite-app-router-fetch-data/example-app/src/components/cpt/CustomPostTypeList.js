"use client";

import { useState } from "react";
import { fetchGraphQL } from "@/lib/client";
import MovieListingItem from "./MovieListingItem";
import { LoadMoreButton } from "../button/LoadMoreButton";

export default function CustomPostTypeList({
  initialPosts,
  initialPageInfo,
  postsPerPage,
  postsQuery,
  siteKey,
  customPostType,
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
      const data = await fetchGraphQL(postsQuery, siteKey, {
        first: postsPerPage,
        after: pageInfo.endCursor,
      });

      const newPosts = data[customPostType]?.edges || [];
      const newPageInfo = data[customPostType]?.pageInfo || {};

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
        {/* Add your own templates here for different cpt */}
        {posts.map(({ node }) =>
          customPostType === "movies" ? (
            <MovieListingItem key={node.id} post={node} />
          ) : null,
        )}
      </div>

      {pageInfo.hasNextPage && (
        <LoadMoreButton
          text="Load More Listings"
          onClick={loadMorePosts}
          loading={loading}
        />
      )}
    </>
  );
}
