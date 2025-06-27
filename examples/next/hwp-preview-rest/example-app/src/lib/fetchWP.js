export async function fetchWP(query, authToken) {
  const WP_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;
  const reqUrl = `${WP_URL}/wp-json/wp/v2/${query}`;

  const res = await fetch(reqUrl, {
    headers: {
      "Content-Type": "application/json",
      ...(authToken ? { Authorization: `Bearer ${authToken}` } : {}),
    },
  });

  if (!res.ok) {
    console.error(`Failed to fetch from ${reqUrl}: ${res.status} ${res.statusText}`);
    return null;
  }

  return res.json();
}
