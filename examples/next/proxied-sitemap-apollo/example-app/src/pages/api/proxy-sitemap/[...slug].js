const wordpressUrl = (
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8888"
).trim();
export default async function handler(req, res) {
  const slug = req.query.slug || [];

  // Reconstruct the original WordPress sitemap path
  const wpPath = slug.join("/");
  let wpUrl = `${wordpressUrl}/${wpPath}.xml`;

  if (slug.length === 0 || slug[0] === "sitemap.xml") {
    wpUrl = `${wordpressUrl}/wp-sitemap.xml`;
  } else {
    const wpPath = slug.join("/");
    wpUrl = `${wordpressUrl}/${wpPath}.xml`;
  }

  console.debug("Fetching sitemap", wpUrl);
  try {
    const wpRes = await fetch(wpUrl);
    console.debug("Fetching sitemap", wpRes);
    if (!wpRes.ok) {
      return res.status(wpRes.status).send("Error fetching original sitemap");
    }

    const contentType = wpRes.headers.get("content-type") || "application/xml";
    let body = await wpRes.text();
    body = body.replace(/<\?xml-stylesheet[^>]*\?>\s*/g, "");

    res.setHeader("Content-Type", contentType);
    res.status(200).send(body);
  } catch (err) {
    res.status(500).send("Internal Proxy Error");
  }
}
