export function renderEntries(entry) {
  const { lastmod, loc } = entry ?? {};

  const lastmodString = lastmod ? `<lastmod>${new Date(lastmod).toISOString()}</lastmod>` : "";

  return `<url>
    <loc>${loc}</loc>
    ${lastmodString}
  </url>`;
}
