"use client";

import { useState } from "react";
import { fetchGraphQL } from "@/lib/client";
import EventListItem from "./EventListItem";

export default function CustomPostTypeList({
  initialPosts,
  initialPageInfo,
  postsPerPage,
  postsQuery,
  customPostType,
}) {
  // Track various states for posts, page info, and loading
  const [posts, setPosts] = useState(initialPosts || []);
  const [pageInfo, setPageInfo] = useState(initialPageInfo || {});
  const [loading, setLoading] = useState(false);

  const loadMorePosts = async () => {
    if (!pageInfo.hasNextPage) return;

    setLoading(true);
    try {
      const data = await fetchGraphQL(postsQuery, {
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
      <div className="grid post-list gap-4">
        {/* Add your own templates here for different cpt */}
        {posts.map(({ node }) =>
          customPostType === "events" ? (
            <EventListItem key={node.id} post={node} />
          ) : null,
        )}
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
