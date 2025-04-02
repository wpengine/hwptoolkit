// pages/sitemap.xml.js
import { DOMParser, XMLSerializer } from 'xmldom';

export async function getServerSideProps({ res }) {
  // Get URLs from environment variables
  const wordpressUrl = process.env.NEXT_PUBLIC_WORDPRESS_URL || 'http://localhost:8888';
  const nextJsUrl = process.env.NEXT_PUBLIC_FRONTEND_URL || 'http://localhost:3000';
  
  try {
    // Fetch the sitemap from WordPress
    const response = await fetch(`${wordpressUrl}/sitemap.xml`);
    
    if (!response.ok) {
      throw new Error(`Failed to fetch sitemap: ${response.status}`);
    }
    
    // Get the XML content
    const xmlString = await response.text();
    
    // Parse the XML
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(xmlString, 'text/xml');
    
    // Determine if this is a sitemap index or a regular sitemap
    const sitemapIndex = xmlDoc.getElementsByTagName('sitemapindex');
    const urlset = xmlDoc.getElementsByTagName('urlset');
    
    if (sitemapIndex.length > 0) {
      // Handle sitemap index
      const sitemaps = xmlDoc.getElementsByTagName('sitemap');
      for (let i = 0; i < sitemaps.length; i++) {
        const locElements = sitemaps[i].getElementsByTagName('loc');
        if (locElements.length > 0) {
          const loc = locElements[0];
          // Replace WordPress domain with Next.js domain in sitemap URLs
          if (loc.textContent.includes(wordpressUrl)) {
            loc.textContent = loc.textContent.replace(wordpressUrl, nextJsUrl);
          }
        }
      }
    } else if (urlset.length > 0) {
      // Handle regular sitemap
      const urls = xmlDoc.getElementsByTagName('url');
      for (let i = 0; i < urls.length; i++) {
        const locElements = urls[i].getElementsByTagName('loc');
        if (locElements.length > 0) {
          const loc = locElements[0];
          // Replace WordPress domain with Next.js domain in page/post URLs
          if (loc.textContent.includes(wordpressUrl)) {
            loc.textContent = loc.textContent.replace(wordpressUrl, nextJsUrl);
          }
        }
      }
    }
    
    // Serialize back to XML string
    const serializer = new XMLSerializer();
    const modifiedXmlString = serializer.serializeToString(xmlDoc);
    
    // Set the appropriate content type
    res.setHeader('Content-Type', 'text/xml');
    res.write(modifiedXmlString);
    res.end();
    
    return {
      props: {},
    };
  } catch (error) {
    console.error('Error processing sitemap:', error);
    res.statusCode = 500;
    res.write('Error generating sitemap');
    res.end();
    
    return {
      props: {},
    };
  }
}

export default function Sitemap() {
  // This component won't be rendered
  return null;
}