import { XMLParser } from 'fast-xml-parser';

const wordpressUrl =
  (process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8888").trim();
const frontEndUrl =
  (process.env.NEXT_PUBLIC_FRONTEND_URL || "http://localhost:3000").trim();

// Parser configuration
const parserConfig = {
  ignoreAttributes: false,
  preserveOrder: false,
  unpairedTags: ['xml', 'xml-stylesheet'],
  processEntities: true,
  htmlEntities: true,
};

function trimSlashes(str) {
  return str.replace(/^\/+|\/+$/g, '');
}

function createSitemap(urls) {
  const sitemap = `<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="${frontEndUrl}/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  ${urls.map(url => `
  <url>
    <loc>${url.loc}</loc>
    ${url.lastmod ? `<lastmod>${url.lastmod}</lastmod>` : ''}
    ${url.changefreq ? `<changefreq>${url.changefreq}</changefreq>` : ''}
    ${url.priority ? `<priority>${url.priority}</priority>` : ''}
  </url>`).join('')}
</urlset>`;

  return sitemap;
}

function createSitemapIndex(sitemaps) {
  const sitemapIndex = `<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="${frontEndUrl}/sitemap.xsl"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  ${sitemaps.map(sitemap => `
  <sitemap>
    <loc data-real-url="${sitemap.realUrl || sitemap.loc}">${sitemap.loc}</loc>
    ${sitemap.lastmod ? `<lastmod>${sitemap.lastmod}</lastmod>` : ''}
  </sitemap>`).join('')}
</sitemapindex>`;

  return sitemapIndex;
}

export async function getServerSideProps({ req, res }) {
  const url = new URL(req.url, `http://${req.headers.host}`);
  const sitemapParam = url.searchParams.get("sitemap");

  const wpSitemapUrl = sitemapParam
    ? `${trimSlashes(wordpressUrl)}/${sitemapParam.replace(/^\/+/, '')}`
    : `${trimSlashes(wordpressUrl)}/sitemap.xml`;
  
  console.debug("Fetching sitemap", wpSitemapUrl);
  const response = await fetch(wpSitemapUrl);

  if (!response.ok) {
    return { notFound: true };
  }

  const xmlText = await response.text();
  const isIndex = xmlText.includes("<sitemapindex");
  const parser = new XMLParser({
    ...parserConfig,
    isArray: (tagName) => tagName === (isIndex ? 'sitemap' : 'url'),
  });

  const parsedXml = parser.parse(xmlText);

  if (isIndex) {
    const wpSitemaps = parsedXml?.sitemapindex?.sitemap;
    if (!wpSitemaps) return { notFound: true };

    const sitemaps = wpSitemaps.map(sitemap => {
      const url = new URL(sitemap.loc);
      const wpPath = url.pathname;
      const filenameMatch = wpPath.match(/\/([^\/]+)\.xml$/);
      
      let displayLoc = filenameMatch && filenameMatch[1]
        ? `${trimSlashes(frontEndUrl)}/sitemaps/${filenameMatch[1]}`
        : `${trimSlashes(frontEndUrl)}/sitemap.xml?sitemap=${wpPath}`;

      return {
        ...sitemap,
        loc: displayLoc,
        realUrl: `${trimSlashes(frontEndUrl)}/sitemap.xml?sitemap=${wpPath}`
      };
    });

    const updatedXml = createSitemapIndex(sitemaps);
    res.setHeader("Content-Type", "application/xml");
    res.write(updatedXml);
    res.end();
  } else {
    const wpUrls = parsedXml?.urlset?.url;
    if (!wpUrls) return { notFound: true };

    const urls = wpUrls.map(url => ({
      ...url,
      loc: url.loc.replace(trimSlashes(wordpressUrl), trimSlashes(frontEndUrl))
    }));

    const updatedXml = createSitemap(urls);
    res.setHeader("Content-Type", "application/xml");
    res.write(updatedXml);
    res.end();
  }

  return { props: {} };
}

export default function Sitemap() {
  return null;
}
