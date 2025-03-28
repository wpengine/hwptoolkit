import { BlogPostItem } from "@/components/BlogPostItem";
import { client } from "@/lib/client";
import { gql } from "@apollo/client";
import Head from "next/head";

const LIST_POSTS = gql`
  query ListPosts {
    posts(first: 20) {
      edges {
        node {
          id
          title
          uri
          excerpt
          date
          featuredImage {
            node {
              sourceUrl(size: LARGE)
              caption
            }
          }
        }
      }
    }
  }
`;

export default function Blog({ data }) {
  return (
    <>
      <Head>
        <title>Home</title>
      </Head>

      {data?.posts?.edges?.map((item) => {
        const post = item.node;

        return <BlogPostItem key={post.id} post={post} />;
      })}
    </>
  );
}

// Fetch the initial list of posts at request time using getServerSideProps
export async function getServerSideProps() {
  const { data } = await client.query({ query: LIST_POSTS });

  return {
    props: {
      data,
    },
  };
}
