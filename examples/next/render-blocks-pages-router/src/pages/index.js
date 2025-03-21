import { BlockRenderer } from "@/components/BlockRenderer";
import blocks from "@/components/blocks";
import { flatListToHierarchical } from "@/utils/flatListToHierarchical";
import Head from "next/head";
import { useEffect, useState } from "react";

// This is an example of a GraphQL query that fetches a post by its URI
// Query includes fragments for each block type
const mockQuery = `
  ${blocks.CoreAudio.fragments.entry}
  ${blocks.CoreButton.fragments.entry}
  ${blocks.CoreButtons.fragments.entry}
  ${blocks.CoreCode.fragments.entry}
  ${blocks.CoreColumn.fragments.entry}
  ${blocks.CoreColumns.fragments.entry}
  ${blocks.CoreHeading.fragments.entry}
  ${blocks.CoreImage.fragments.entry}
  ${blocks.CoreList.fragments.entry}
  ${blocks.CoreListItem.fragments.entry}
  ${blocks.CoreParagraph.fragments.entry}
  ${blocks.CorePreformatted.fragments.entry}
  ${blocks.CoreQuote.fragments.entry}
  ${blocks.CoreSeparator.fragments.entry}
  ${blocks.CoreTable.fragments.entry}
  ${blocks.CoreVideo.fragments.entry}
  query GetPost($uri: ID!) {
    post(id: $uri, idType: URI) {
      title
      content
      editorBlocks {
        name
        __typename
        renderedHtml
        id: clientId
        parentId: parentClientId
        ...${blocks.CoreAudio.fragments.key}
        ...${blocks.CoreButton.fragments.key}
        ...${blocks.CoreButtons.fragments.key}
        ...${blocks.CoreCode.fragments.key}
        ...${blocks.CoreColumn.fragments.key}
        ...${blocks.CoreColumns.fragments.key}
        ...${blocks.CoreHeading.fragments.key}
        ...${blocks.CoreImage.fragments.key}
        ...${blocks.CoreList.fragments.key}
        ...${blocks.CoreListItem.fragments.key}
        ...${blocks.CoreParagraph.fragments.key}
        ...${blocks.CorePreformatted.fragments.key}
        ...${blocks.CoreQuote.fragments.key}
        ...${blocks.CoreSeparator.fragments.key}
        ...${blocks.CoreTable.fragments.key}
        ...${blocks.CoreVideo.fragments.key}
      }
    }
  }
`;

const mockRequest = (/* query, postURI */) => {
  const REQUEST_URL = "/post.json";

  // This is a mock fetch call that simulates an API call to fetch a post
  // It fetches a post data from a JSON file, located in the public directory
  // In a real-world scenario, you would replace REQUEST_URL with the actual WordPress GraphQL endpoint and POST_URI with actual post URI
  return fetch(REQUEST_URL, {
    // method: "POST",
    // headers: {
    //   "Content-Type": "application/json",
    // },
    // body: JSON.stringify({
    //   query,
    //   variables: {
    //     uri: postURI,
    //   },
    // }),
  }).then((res) => res.json());
};

// This is an example parser using `html-react-parser` to demonstrate how to pass a custom parser to the BlockRenderer
// const customParser = (content) => parse(content, {});

// This is an example custom default block component to override DefaultBlock component
// function CustomDefaultBlock({ block }) {
//   return <p>{block?.__typename} is not supported</p>;
// }

export default function Home() {
  const [post, setPost] = useState();
  // GraphQL response is a flat list of blocks, we need to convert it to a hierarchical structure
  const hierarchicalEditorBlocks = flatListToHierarchical(post?.editorBlocks);

  useEffect(() => {
    mockRequest(mockQuery, "render-blocks").then((data) => setPost(data.data.post));
  }, []);

  return (
    <>
      <Head>
        <title>{post?.title}</title>
      </Head>

      <main>
        <h1>{post?.title}</h1>
        <BlockRenderer
          blocks={hierarchicalEditorBlocks}
          // defaultBlock={CustomDefaultBlock}
          // customParser={customParser}
        />
      </main>
    </>
  );
}
