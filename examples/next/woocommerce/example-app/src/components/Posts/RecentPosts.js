import { gql } from "@apollo/client";
import { useRouteData } from "@/lib/templates/context";

export default function RecentPosts() {
  const { graphqlData } = useRouteData();

  const posts = graphqlData?.RecentPosts?.data?.posts?.nodes || [];

  if (graphqlData?.RecentPosts?.error) {
    console.error("Error fetching RecentPosts:", graphqlData.RecentPosts.error);
    return <div>Error loading recent posts.</div>;
  }

  return (
    <div className="recent-posts">
      <h2>Recent Posts</h2>
      <ul>
        {posts.map((post) => (
          <li key={post.id}>
            <a href={post.uri}>{post.title}</a>
          </li>
        ))}
      </ul>
    </div>
  );
}

RecentPosts.query = {
  query: gql`
    query RecentPosts {
      posts(first: 5) {
        nodes {
          id
          title
          uri
        }
      }
    }
  `,
};
 