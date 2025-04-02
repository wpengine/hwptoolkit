import { DOMParser, XMLSerializer } from "@xmldom/xmldom";

const wordpressUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8888";
const frontEndUrl =
  process.env.NEXT_PUBLIC_FRONTEND_URL || "http://localhost:3000";

export async function getServerSideProps({ req, res }) {
  const url = new URL(req.url, `http://${req.headers.host}`);
  const sitemapParam = url.searchParams.get("sitemap");

  const wpSitemapUrl = sitemapParam
    ? `${wordpressUrl.replace(/\/$/, '')}${sitemapParam.startsWith('/') ? sitemapParam : `/${sitemapParam}`}`
    : `${wordpressUrl.replace(/\/$/, '')}/sitemap.xml`;
  console.debug("Fetching sitemap", wpSitemapUrl);
  const response = await fetch(wpSitemapUrl);

  if (!response.ok) {
    return { notFound: true };
  }

  const xmlText = await response.text();
  const doc = new DOMParser().parseFromString(xmlText, "text/xml");

  // Remove the xml-stylesheet node if it exists
  const nodesToRemove = [];
  for (let i = 0; i < doc.childNodes.length; i++) {
    const node = doc.childNodes[i];
    if (node.nodeType === 7 && node.nodeName === "xml-stylesheet") {
      nodesToRemove.push(node);
    }
  }
  nodesToRemove.forEach((node) => doc.removeChild(node));
  const isIndex = doc.getElementsByTagName("sitemapindex").length > 0;

  const newStylesheetPI = doc.createProcessingInstruction(
    "xml-stylesheet",
    `type="text/xsl" href="${frontEndUrl}/sitemap.xsl"`
  );
  doc.insertBefore(newStylesheetPI, doc.childNodes[0].nextSibling);

  if (isIndex) {
    const sitemaps = doc.getElementsByTagName("sitemap");
    for (let i = 0; i < sitemaps.length; i++) {
      const locNode = sitemaps[i].getElementsByTagName("loc")[0];
      if (locNode) {
        const wpPath = new URL(locNode.textContent).pathname;
        // Extract the filename without the path and extension
        const filenameMatch = wpPath.match(/\/([^\/]+)\.xml$/);
        if (filenameMatch && filenameMatch[1]) {
          const displayName = filenameMatch[1];
          // Store the actual link in a custom attribute for the XSL to use
          // But display a cleaner URL text
          locNode.textContent = `${frontEndUrl}/sitemaps/${displayName}`;
          
          // We'll add a hidden attribute that the XSL can use to get the real URL
          const realUrlAttr = doc.createAttribute("data-real-url");
          realUrlAttr.value = `${frontEndUrl}/sitemap.xml?sitemap=${wpPath}`;
          locNode.attributes.setNamedItem(realUrlAttr);
        } else {
          // Fallback to the old method if the pattern doesn't match
          locNode.textContent = `${frontEndUrl}/sitemap.xml?sitemap=${wpPath}`;
        }
      }
    }
  } else {
    const urls = doc.getElementsByTagName("url");
    for (let i = 0; i < urls.length; i++) {
      const locNode = urls[i].getElementsByTagName("loc")[0];
      if (locNode) {
        locNode.textContent = locNode.textContent.replace(
          wordpressUrl,
          frontEndUrl
        );
      }
    }
  }

  const updatedXml = new XMLSerializer().serializeToString(doc);
  res.setHeader("Content-Type", "application/xml");
  res.write(updatedXml);
  res.end();

  return { props: {} };
}

export default function Sitemap() {
  return null;
}