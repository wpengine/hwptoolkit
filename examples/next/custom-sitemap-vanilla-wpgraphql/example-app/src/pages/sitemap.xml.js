import { client } from "@/lib/client";
import { generateSiteMap } from "@/lib/generateSiteMap";
import { gql } from "@apollo/client";

// Get public URL from environment variables
const publicUrl = process.env.NEXT_PUBLIC_URL;

// This query will get all the sitemap subTypes
// and return them with the parent type and page counts information
const LIST_SITEMAP_TYPES = gql`
  query ListSitemapTypes {
    sitemapTypes {
      type
      subType
      pages
    }
  }
`;

// Render a separate link for each subType and page
function renderTypes(item) {
  const { type, subType, pages } = item ?? {};

  return [...Array(pages)].map(
    (_, index) => `
      <url>
        <loc>${`${publicUrl}/sitemap/${type}/${subType}/${index + 1}.xml`}</loc>
      </url>
    `
  );
}

export default function SiteMap() {
  // XML content will be generated in getServerSideProps
}

// More info: https://nextjs.org/learn/seo/xml-sitemaps
export async function getServerSideProps({ res, req }) {
  // In this function we will form our index sitemap
  const data = await client.query({ query: LIST_SITEMAP_TYPES });
  const subTypes = data?.data?.sitemapTypes ?? [];

  // Create text content of XML sitemap
  const sitemap = generateSiteMap(subTypes, renderTypes);

  // Set the response header to XML and respond with the generated sitemap
  res.setHeader("Content-Type", "text/xml");
  res.write(sitemap);
  res.end();

  return {
    props: {},
  };
}
