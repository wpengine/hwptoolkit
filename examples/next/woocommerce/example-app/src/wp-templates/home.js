import Posts from "@/components/Posts/Posts";

export default function Home() {
  return (
    <>
      <Posts />
    </>
  );
}

Home.queries = [
  Posts.query,
];
