export async function fetchGraphQL(query, variables = {}, revalidate = null) {
  try {
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

    const response = await fetch(
      `${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`,
      fetchOptions,
    );

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
