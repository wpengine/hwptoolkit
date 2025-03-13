import { notFound } from 'next/navigation';
import { SinglePageFragment } from '@/lib/fragments/SinglePageFragment';
import { SinglePostFragment } from '@/lib/fragments/SinglePostFragment';
import Page from "@/components/single/Page";
import Post from "@/components/single/Post";
import { fetchGraphQL } from '@/lib/client';

const GET_CONTENT_QUERY = `
  ${SinglePageFragment}
  ${SinglePostFragment}
  query GetNodeByUri($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      ...SinglePageFragment
      ...SinglePostFragment
    }
  }
`;

async function fetchContent(uri) {
    return await fetchGraphQL(GET_CONTENT_QUERY, {
        uri: uri
    }, 3600);
}

export default async function ContentPage({ params }) {

  const uri = Array.isArray(params?.uri) ? params.uri.join('/') : '';
  const data = await fetchContent(uri);

  if (!data?.nodeByUri) {
    console.warn('No nodeByUri data found, returning 404');
    notFound();
  }

  const contentType = data?.nodeByUri?.__typename;

    if (contentType === "Post") return <Post data={data.nodeByUri} />;
    if (contentType === "Page") return <Page data={data.nodeByUri} />;
    notFound();
}

// Note: We could generate static params for the pages you want to pre-render (optional) for things like popular posts etc
export async function generateStaticParams() {
  return [];
}
