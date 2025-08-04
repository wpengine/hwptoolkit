import Layout from "@/components/Layout";
import RecentPosts from "@/components/RecentPosts";

export default function Home() {
  return (
    <Layout>
      <h2>Home Template</h2>
      <p>This is the home page of the template hierarchy example app.</p>
      <RecentPosts />
    </Layout>
  );
}

Home.queries = [
  RecentPosts.query, // Ensure RecentPosts query is included
];
