// @TODO tidy up
export async function fetchGraphQL(query, variables = {}) {
  try {
    const response = await fetch(`${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query,
        variables,
      }),
    });

    const result = await response.json();

    if (result.errors) {
      console.error('GraphQL Error:', result.errors);
      throw new Error('Failed to fetch data from WordPress');
    }

    return result.data;
  } catch (error) {
    console.error('Error fetching from WordPress:', error);
    throw error;
  }
}
