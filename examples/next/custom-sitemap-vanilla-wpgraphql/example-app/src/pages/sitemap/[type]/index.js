import { generateSiteMap } from "@/lib/generateSiteMap";
import { getPaginatedQuery } from "@/lib/getPaginatedQuery";
import { renderEntries } from "@/lib/renderEntries";
import { queries } from "@/queries";

const publicUrl = process.env.NEXT_PUBLIC_URL;
const PER_PAGE = 100; // Number of nodes per page

export default function Sitemap() {
  // XML content will be generated in getServerSideProps
}

export async function getServerSideProps({ query, res }) {
  let sitemapType = query?.type;

  // Check if the sitemap type is provided and is a valid XML format
  if (!sitemapType || !sitemapType.includes(".xml")) {
    return {
      notFound: true,
    };
  }

  // Remove the ".xml" extension from the sitemap type
  sitemapType = sitemapType.replace(/\.xml$/, "");

  const allowedSitemapTypes = Object.keys(queries);
  const isSitemapTypeAllowed = allowedSitemapTypes.includes(sitemapType);

  if (!isSitemapTypeAllowed) {
    return {
      notFound: true,
    };
  }

  // Fetch the nodes for the specified sitemap type
  const nodes = await getPaginatedQuery(
    {
      query: queries[sitemapType],
      variables: { first: PER_PAGE },
    },
    sitemapType
  );

  const entries =
    nodes?.map((node) => ({
      loc: new URL(node.uri, publicUrl).href,
      lastmod: node.modified,
    })) ?? [];

  // Generate the sitemap using the entries and the render function
  const sitemap = generateSiteMap(entries, renderEntries);

  // Set the response header to XML and respond with the generated sitemap
  res.setHeader("Content-Type", "text/xml");
  res.write(sitemap);
  res.end();

  return {
    props: {},
  };
}
