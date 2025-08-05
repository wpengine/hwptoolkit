import { BlogPostItem } from "@/components/BlogPostItem";
import Head from "next/head";

export default function Archive({ posts, type, title }) {
  const pageTitle = type + ": " + title;

  return (
    <>
      <Head>
        <title>{pageTitle}</title>
      </Head>

      <h1 className='container max-w-4xl py-6 mx-auto mb-4 text-2xl font-bold'>{pageTitle}</h1>

      {posts?.map((item) => {
        const post = item.node;

        return <BlogPostItem key={post.id} post={post} />;
      })}
    </>
  );
}