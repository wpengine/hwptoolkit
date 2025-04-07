import { generateSiteMap } from "@/lib/generateSiteMap";
import { queries } from "@/queries";

// Get public URL from environment variables
const publicUrl = process.env.NEXT_PUBLIC_URL;

// Render a separate link for each subType and page
function renderTypes(item) {
  return `<url>
    <loc>${`${publicUrl}/sitemap/${item}.xml`}</loc>
  </url>`;
}

export default function SiteMap() {
  // XML content will be generated in getServerSideProps
}

// More info: https://nextjs.org/learn/seo/xml-sitemaps
export async function getServerSideProps({ res }) {
  const allowedSitemapTypes = Object.keys(queries);

  // Create text content of XML sitemap
  const sitemap = generateSiteMap(allowedSitemapTypes, renderTypes);

  // Set the response header to XML and respond with the generated sitemap
  res.setHeader("Content-Type", "text/xml");
  res.write(sitemap);
  res.end();

  return {
    props: {},
  };
}
