import { client } from "@/lib/client";
import { generateSiteMap } from "@/lib/generateSiteMap";
import { gql } from "@apollo/client";

// Get public URL from environment variables
const publicUrl = process.env.NEXT_PUBLIC_URL;

// This query will get all the urls for given type and subType and page number
const LIST_SITEMAP_ENTRIES = gql`
  query ListSitemapEntries($type: String!, $subType: String!, $page: Int!) {
    sitemapEntries(type: $type, subType: $subType, page: $page) {
      uri
      lastmod
      imageLoc
    }
  }
`;

function renderEntries(entry) {
  const { imageLoc, lastmod, uri } = entry ?? {};
  // We're also adding featured images when available
  // More info: https://developers.google.com/search/docs/crawling-indexing/sitemaps/image-sitemaps
  const imageString = imageLoc
    ? `<image:image>
        <image:loc>${imageLoc}</image:loc>
      </image:image>`
    : "";

  const lastmodString = lastmod ? `<lastmod>${lastmod}</lastmod>` : "";

  return `<url>
            <loc>${publicUrl}${uri}</loc>
            ${lastmodString}
            ${imageString}
          </url>`;
}

export default function SiteMap() {
  // XML content will be generated in getServerSideProps
}

export async function getServerSideProps({ query, res }) {
  // We will receive the type, subType, and page from the query
  const [type, subType, page] = query?.type ?? [];
  const hasXmlExtension = page?.endsWith(".xml"); // Make sure the URL ends with .xml

  if (type && subType && hasXmlExtension) {
    // Get all the URLs for the given type, subType, and page
    const data = await client.query({
      query: LIST_SITEMAP_ENTRIES,
      variables: { type, subType, page: parseInt(page, 10) },
    });
    const entries = data?.data?.sitemapEntries ?? [];

    const sitemap = generateSiteMap(entries, renderEntries);

    // Set the response header to XML and respond with the generated sitemap
    res.setHeader("Content-Type", "text/xml");
    res.write(sitemap);
    res.end();

    return {
      props: {},
    };
  }

  return {
    notFound: true,
  };
}
