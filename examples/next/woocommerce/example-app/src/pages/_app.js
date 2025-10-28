import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import getApolloClient from "@/lib/getApolloClient";
import Layout from "@/components/Layout";
import { AuthProvider, useAuth } from "@/lib/auth/AuthProvider";
import { CartProvider } from "@/lib/woocommerce/CartProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import "@/styles/globals.scss";

// Wait for auth to load, and then load the full app.
// Cart will be loaded in the background, not crucial.
function AppContent({ Component, pageProps }) {
	const router = useRouter();
	const { isLoading } = useAuth();

	// Wait ONLY for auth - cart loads in background
	if (isLoading) {
		return <LoadingSpinner />;
	}

	return (
		<Layout pageProps={pageProps}>
			<Component {...pageProps} key={router.asPath} />
		</Layout>
	);
}

export default function App({ Component, pageProps }) {
	const client = getApolloClient();

	return (
		<ApolloProvider client={client}>
			<AuthProvider>
				<CartProvider>
					<AppContent Component={Component} pageProps={pageProps} />
				</CartProvider>
			</AuthProvider>
		</ApolloProvider>
	);
}
