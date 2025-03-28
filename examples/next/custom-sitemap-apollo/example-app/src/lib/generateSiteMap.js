// Generate XML content for sitemap.xml
export function generateSiteMap(entries = [], renderEntries = () => "") {
  return `<?xml version="1.0" encoding="UTF-8"?>
    <?xml-stylesheet type="text/xsl" href="/sitemap.xsl" ?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
      ${entries?.map(renderEntries).join("")}
    </urlset>`;
}
