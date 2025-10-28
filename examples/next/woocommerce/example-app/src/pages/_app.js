import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import getApolloClient from "@/lib/getApolloClient";
import Layout from "@/components/Layout";
import { AuthProvider, useAuth } from "@/lib/auth/AuthProvider";
import { CartProvider } from "@/lib/woocommerce/CartProvider";
import "@/styles/globals.scss";

// Loading screen component
function AppLoadingScreen() {
	return (
		<div className="flex items-center justify-center min-h-screen bg-gray-50">
			<div className="text-center">
				<div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
				<p className="text-gray-600 text-lg font-medium">Loading...</p>
			</div>
		</div>
	);
}
//Wait for auth to load, and then load the full app. Cart will be loaded in the background, not crucial.
function AppContent({ Component, pageProps }) {
    const router = useRouter();
    const { isLoading } = useAuth();

    // Wait ONLY for auth - cart loads in background
    if (isLoading) {
        return <AppLoadingScreen />;
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