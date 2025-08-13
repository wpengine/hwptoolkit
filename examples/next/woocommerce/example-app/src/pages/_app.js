import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import { AuthProvider } from "@/lib/auth/AuthProvider";
import getApolloClient from "@/lib/getApolloClient";
import { CartProvider } from "@/lib/woocommerce/cartContext"; 
import Layout from "@/components/Layout";
import "@/styles/globals.scss";

export default function App({ Component, pageProps }) {
  const router = useRouter();
  const client = getApolloClient();

  return (
    <ApolloProvider client={client}>
      <AuthProvider>
        <CartProvider>
          <Layout pageProps={pageProps}>
            <Component {...pageProps} key={router.asPath} />
          </Layout>
        </CartProvider>
      </AuthProvider>
    </ApolloProvider>
  );
}
