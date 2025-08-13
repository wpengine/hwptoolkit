import RecentPosts from "@/components/Posts/RecentPosts";

export default function Home() {
  return (
    <>
      <h1>Blog Template</h1>
      <RecentPosts />
    </>
  );
}

Home.queries = [
  RecentPosts.query, // Ensure RecentPosts query is included
];
