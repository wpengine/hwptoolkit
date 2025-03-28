// Catch all template
import { notFound } from "next/navigation";
import { SingleEventFragment } from "@/lib/fragments/SingleEventFragment";
import { SinglePageFragment } from "@/lib/fragments/SinglePageFragment";
import { SinglePostFragment } from "@/lib/fragments/SinglePostFragment";
import Page from "@/components/single/Page";
import Post from "@/components/single/Post";
import Event from "@/components/single/Event";
import { fetchGraphQL } from "@/lib/client";

// See WPGraphQL docs on nodeByUri: https://www.wpgraphql.com/2021/12/23/query-any-page-by-its-path-using-wpgraphql
const GET_CONTENT_QUERY = `
  ${SingleEventFragment}
  ${SinglePageFragment}
  ${SinglePostFragment}
  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...SinglePageFragment
      ...SinglePostFragment
      ...SingleEventFragment
    }
  }
`;

async function fetchContent(uri) {
  return await fetchGraphQL(
    GET_CONTENT_QUERY,
    {
      uri: uri,
    },
    3600, // Caches for 60 minutes
  );
}

export default async function ContentPage({ params }) {
  // Await for the params to resolve
  const resolvedParams = await params;

  const uri = Array.isArray(resolvedParams?.uri)
    ? resolvedParams.uri.join("/")
    : "";
  const data = await fetchContent(uri);

  if (!data?.nodeByUri) {
    console.warn("No nodeByUri data found, returning 404");
    notFound();
  }

  const contentType = data?.nodeByUri?.__typename;

  // Add your own CPT templates here for single post types
  if (contentType === "Post") return <Post data={data.nodeByUri} />;
  if (contentType === "Page") return <Page data={data.nodeByUri} />;
  if (contentType === "Event") return <Event data={data.nodeByUri} />;
  notFound();
}

// Note: We could generate static params for the pages you want to pre-render (optional) for things like popular posts etc
export async function generateStaticParams() {
  return [];
}
