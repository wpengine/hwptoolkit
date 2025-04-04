export async function fetchGraphQL(
  query,
  siteKey,
  variables = {},
  revalidate = null,
) {
  try {
    const endpoint = process.env.WORDPRESS_SITES
      ? JSON.parse(process.env.WORDPRESS_SITES)[siteKey]
      : null;

    if (!endpoint) {
      throw new Error(`Endpoint for site "${siteKey}" not found.`);
    }

    const fetchOptions = {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        query,
        variables,
      }),
    };

    // Revalidate with ISR if revalidate is set
    if (revalidate !== null) {
      fetchOptions.next = {
        revalidate: revalidate,
      };
    }

    const response = await fetch(endpoint, fetchOptions);

    const result = await response.json();

    if (result.errors) {
      console.error("GraphQL Error:", result.errors);
      throw new Error("Failed to fetch data from WordPress");
    }

    return result.data;
  } catch (error) {
    console.error("Error fetching from WordPress:", error);
    throw error;
  }
}
