import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import { AppProvider } from "@/lib/AppProvider";
import getApolloClient from "@/lib/getApolloClient";
import Layout from "@/components/Layout";
import "@/styles/globals.scss";

export default function App({ Component, pageProps }) {
  const router = useRouter();
  const client = getApolloClient();

  return (
    <ApolloProvider client={client}>
      <AppProvider>
        <Layout pageProps={pageProps}>
          <Component {...pageProps} key={router.asPath} />
        </Layout>
      </AppProvider>
    </ApolloProvider>
  );
} 