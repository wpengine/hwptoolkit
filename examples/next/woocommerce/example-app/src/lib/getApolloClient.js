import { ApolloClient, InMemoryCache, ApolloLink, HttpLink, from, Observable } from "@apollo/client";
import { onError } from "@apollo/client/link/error";
import useLocalStorage from "./storage";
import { GetCartDocument } from "./graphQL/userGraphQL";
import { GraphQLClient } from "graphql-request";
import { setContext } from "@apollo/client/link/context";
const baseUrl = process.env.NEXT_PUBLIC_WORDPRESS_URL || "http://localhost:8890";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
const SESSION_TOKEN_KEY = process.env.SESSION_TOKEN_LS_KEY || "woocommerce_session_token";

const httpLink = new HttpLink({
	uri: `${baseUrl}${graphqlPath}`,
});

// Session Token Management.
async function fetchSessionToken() {
	let sessionToken;
	try {
		const graphQLClient = new GraphQLClient(`${baseUrl}${graphqlPath}`);

		const cartData = await graphQLClient.request(GetCartDocument);
		// If user doesn't have an account return accountNeeded flag.
		sessionToken = cartData?.customer?.sessionToken;

		if (!sessionToken) {
			throw new Error("Failed to retrieve a new session token");
		}

		// Store the session token
		if (typeof window !== "undefined") {
			localStorage.setItem(SESSION_TOKEN_KEY, sessionToken);
		}
	} catch (err) {
		console.error("Error fetching session token:", err);
	}

	return sessionToken;
}

export async function getSessionToken(forceFetch = false) {
	if (typeof window === "undefined") return null;

	let sessionToken = localStorage.getItem(SESSION_TOKEN_KEY);

	if (!sessionToken || forceFetch) {
		sessionToken = await fetchSessionToken();
	}

	return sessionToken;
}

// Unified auth link - handles auth token for both logged-in users and cart sessions
function createAuthLink() {
	return setContext(async (operation) => {
		if (typeof window === "undefined") return {};

		const storage = useLocalStorage;
		const storedTokens = storage.getItem("authTokens");
		const headers = {};

		if (storedTokens) {
			try {
				const tokens = JSON.parse(storedTokens);

				if (tokens?.authToken) {
					headers.Authorization = `Bearer ${tokens.authToken}`;
				}
			} catch (error) {
				console.error("Error parsing auth tokens:", error);
				storage.removeItem("authTokens");
			}
		}

		// Add session token for guest users (cart persistence)
		if (!headers.Authorization) {
			const sessionToken = await getSessionToken();
			if (sessionToken) {
				headers["woocommerce-session"] = `Session ${sessionToken}`;
			}
		}

		return { headers };
	});
}

// Error handling link for expired/invalid tokens
function createErrorLink() {
	return onError(({ graphQLErrors, operation, forward }) => {
		const targetErrors = [
			"The iss do not match with this server",
			"invalid-secret-key | Expired token",
			"invalid-secret-key | Signature verification failed",
			"Expired token",
			"Wrong number of segments",
		];

		let observable;

		if (graphQLErrors?.length) {
			for (const { debugMessage, message } of graphQLErrors) {
				if (targetErrors.includes(message) || targetErrors.includes(debugMessage)) {
					console.warn("🔄 Token error detected, fetching new session token...");

					observable = new Observable((observer) => {
						getSessionToken(true)
							.then((sessionToken) => {
								operation.setContext(({ headers = {} }) => {
									const nextHeaders = { ...headers };

									if (sessionToken) {
										nextHeaders["woocommerce-session"] = `Session ${sessionToken}`;
									} else {
										delete nextHeaders["woocommerce-session"];
									}

									return {
										headers: nextHeaders,
									};
								});
							})
							.then(() => {
								const subscriber = {
									next: observer.next.bind(observer),
									error: observer.error.bind(observer),
									complete: observer.complete.bind(observer),
								};
								forward(operation).subscribe(subscriber);
							})
							.catch((error) => {
								observer.error(error);
							});
					});

					break; // Exit loop once we handle the error
				}
			}
		}

		return observable;
	});
}

// Network error handling
const networkErrorLink = onError(({ graphQLErrors, networkError, operation, forward }) => {
	if (networkError) {

		if (networkError.statusCode === 403) {
			console.warn("403 Forbidden - clearing auth tokens");

			if (typeof window !== "undefined") {
				const storage = useLocalStorage;
				storage.removeItem("authTokens");
			}

			return forward(operation);
		}
	}

	if (graphQLErrors) {
		graphQLErrors.forEach(({ message, locations, path }) =>
			console.log(`GraphQL error: Message: ${message}, Location: ${JSON.stringify(locations)}, Path: ${path}`)
		);
	}
});

// Console logging link for debugging
const consoleLink = new ApolloLink((operation, forward) => {
	//console.log(`🔍 GraphQL Operation: ${operation.operationName}`);

	return forward(operation).map((response) => {
		//console.log(`GraphQL Response: ${operation.operationName}`, response);
		return response;
	});
});

export default function getApolloClient() {
	return new ApolloClient({
		link: from([consoleLink, networkErrorLink, createErrorLink(), createAuthLink(), httpLink]),
		cache: new InMemoryCache(),
		defaultOptions: {
			watchQuery: {
				errorPolicy: "ignore",
			},
			query: {
				errorPolicy: "all",
			},
			mutate: {
				errorPolicy: "all",
			},
		},
	});
}
