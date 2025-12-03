import { client } from "@/lib/client";
import { gql } from "@apollo/client";
import Head from "next/head";

const GET_CONTENT = gql`
  query GetContentByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...Page
      ...Post
    }
  }
`;

export default function SingleContent({ content }) {
  if (!content) {
    return (
      <>
        <Head>
          <title>Not Found</title>
        </Head>
        <div className="max-w-4xl mx-auto px-4 py-16">
          <h1 className="text-4xl font-bold mb-4">Content Not Found</h1>
          <p>The requested content could not be found.</p>
        </div>
      </>
    );
  }

  return (
    <>
      <Head>
        <title>{content.title}</title>
      </Head>

      <article className="max-w-4xl mx-auto px-4 py-8">
        {content.featuredImage && (
          <div className="mb-8">
            <img
              src={content.featuredImage.node.sourceUrl}
              alt={content.title}
              className="w-full h-auto rounded-lg shadow-lg"
            />
          </div>
        )}

        <h1 className="text-4xl font-bold mb-4">{content.title}</h1>

        {content.author && (
          <p className="text-gray-600 mb-4">
            By {content.author.node.name} on{" "}
            {new Date(content.date).toLocaleDateString()}
          </p>
        )}

        {content.__typename === "Page" && (
          <p className="text-sm text-gray-500 mb-4">Page</p>
        )}

        <div
          className="prose max-w-none"
          dangerouslySetInnerHTML={{ __html: content.content }}
        />
      </article>
    </>
  );
}

export async function getServerSideProps({ params }) {
  try {
    const { data } = await client.query({
      query: GET_CONTENT,
      variables: { uri: `/${params.slug}` },
    });

    if (!data?.nodeByUri) {
      return {
        notFound: true,
      };
    }

    return {
      props: {
        content: data.nodeByUri,
      },
    };
  } catch (error) {
    console.error("Error fetching content:", error);
    return {
      notFound: true,
    };
  }
}

