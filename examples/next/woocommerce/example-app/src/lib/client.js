import { ApolloClient, HttpLink, InMemoryCache, ApolloLink } from "@apollo/client";
import { createFragmentRegistry } from "@apollo/client/cache";
import { relayStylePagination } from "@apollo/client/utilities";

export async function fetchGraphQL(query, variables) {
	try {
		// console.log('Variables:', variables);

		const body = JSON.stringify({
			query,
			variables: {
				...variables,
			},
		});

		//console.log('📦 Request body:', body);

		const response = await fetch(`${process.env.NEXT_PUBLIC_WORDPRESS_URL}/graphql`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body,
			cache: "default",
			next: {
				tags: ["wordpress"],
			},
		});

		if (!response.ok) {
			const errorText = await response.text();
			// console.error("HTTP Error Details:");
			// console.error("  Status:", response.status);
			// console.error("  Status Text:", response.statusText);
			// console.error("  Response Body:", errorText);
			throw new Error(`HTTP ${response.status}: ${response.statusText} - ${errorText}`);
		}

		const data = await response.json();

		if (data.errors) {
			console.error("GraphQL Errors:", data.errors);
			throw new Error(`GraphQL Error: ${JSON.stringify(data.errors)}`);
		}
		return data;
	} catch (error) {
		// console.error("fetchGraphQL Error Details:");
		// console.error("  Error type:", error.constructor.name);
		// console.error("  Error message:", error.message);
		// console.error("  Error stack:", error.stack);
		throw error;
	}
}

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;
const link = new HttpLink({
	uri: WORDPRESS_URL + "/graphql",
	// useGETForQueries: true,
});

const logLink = new ApolloLink((operation, forward) => {
	return forward(operation);
});
// Initialize Apollo Client with the link and cache configuration
// More info: https://www.apollographql.com/docs/react/api/core/ApolloClient/
export const client = new ApolloClient({
    link: logLink.concat(link),
    ssrMode: typeof window === "undefined",
    cache: new InMemoryCache({
        typePolicies: {
            Query: {
                fields: {
                    posts: relayStylePagination(),                
                    products: relayStylePagination(),
                },
            },
        },
    }),
});

export function gql(strings, ...values) {
	return strings.reduce((result, string, i) => {
		return result + string + (values[i] || "");
	}, "");
}
