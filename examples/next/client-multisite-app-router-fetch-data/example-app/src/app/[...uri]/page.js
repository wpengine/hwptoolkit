// Catch all template
// Please add other components for other post types here too and update the query template
import { notFound } from "next/navigation";
import Page from "@/components/single/Page";
import Post from "@/components/single/Post";
import { fetchGraphQL } from "@/lib/client";
import { NodeByUriQuery } from "@/lib/queries/NodeByUriQuery";

// This function fetches the data for the given uri and siteKey
async function fetchContent(uri, siteKey) {
  return await await fetchGraphQL(
    NodeByUriQuery,
    siteKey,
    {
      uri: uri,
    },
    3600,
  );
}

// This is a catch-all route that will match any path
// We will loop through each site defined in next.config.mjs and loop through each site
// and fetch the data for the uri using the nodeByUri query.
// We will return notFound if the data is not found for any of the sites.

export default async function ContentPage({ params }) {
  // Await for the params to resolve
  const resolvedParams = await params;

  const uri = Array.isArray(resolvedParams?.uri)
    ? resolvedParams.uri.join("/")
    : "";

  const wordpressSites = JSON.parse(process.env.WORDPRESS_SITES || "{}");
  let data, currentSiteKey = null;
  for (const [siteKey] of Object.entries(wordpressSites)) {
    currentSiteKey = siteKey;
    data = await fetchContent(uri, siteKey);

    if (data?.nodeByUri) {
      break;
    }
  }

  if (!data?.nodeByUri) {
    console.warn("No data found, returning 404");
    notFound();
  }

  const contentType = data?.nodeByUri?.__typename;

  // Add your own CPT templates here for single post types
  if (contentType === "Post") return <Post data={data.nodeByUri} siteKey={currentSiteKey} />;
  if (contentType === "Page") return <Page data={data.nodeByUri} siteKey={currentSiteKey} />;
  notFound();
}

// Note: We could generate static params for the pages you want to pre-render (optional) for things like popular posts etc
export async function generateStaticParams() {
  return [];
}
