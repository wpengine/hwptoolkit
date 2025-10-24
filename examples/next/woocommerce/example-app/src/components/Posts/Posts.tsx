import React from "react";
import { useRouteData } from "@/lib/templates/context";
import PostCard from "@/components/Posts/PostCard";
import POSTS_QUERY from "@/components/Posts/postQuery";

interface PostsProps {
  cols?: number;
  loading?: boolean;
  showTitle?: boolean;
  title?: string;
}

export default function Posts({ 
  cols = 3, 
  loading = false,
  showTitle = true,
  title = "Recent Posts"
}: PostsProps) {
  const { graphqlData } = useRouteData();

  const posts = graphqlData?.GetPosts?.posts?.edges || [];

  if (graphqlData?.GetPosts?.error) {
    console.error("Error fetching Posts:", graphqlData.GetPosts.error);
    return <div className="text-center text-red-600 p-4">Error loading posts.</div>;
  }

  if (loading) {
    return (
      <div className="text-center p-8 bg-gray-50 rounded-lg">
        <p className="text-gray-600">Loading posts...</p>
      </div>
    );
  }

  if (!loading && posts.length === 0) {
    return (
      <div className="text-center p-8 bg-red-50 border border-red-200 rounded-lg">
        <p className="text-gray-600">No posts found.</p>
      </div>
    );
  }

  // Get Tailwind grid classes based on column count
  const getGridClass = () => {
    switch (cols) {
      case 1: return "grid-cols-1";
      case 2: return "grid-cols-1 md:grid-cols-2";
      case 3: return "grid-cols-1 md:grid-cols-2 lg:grid-cols-3";
      case 4: return "grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4";
      case 5: return "grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5";
      case 6: return "grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6";
      default: return "grid-cols-1 md:grid-cols-2 lg:grid-cols-3";
    }
  };

  return (
    <div className="my-4">
      {showTitle && (
        <h2 className="text-3xl font-bold text-center text-gray-800 mb-8">
          {title}
        </h2>
      )}
      
      <div className={`grid ${getGridClass()} gap-6`}>
        {posts.map((post) => (
          <PostCard 
            post={post.node} 
            key={post.node.id}
            cols={cols}
          />
        ))}
      </div>
    </div>
  );
}

Posts.query = {
  query: POSTS_QUERY,
};