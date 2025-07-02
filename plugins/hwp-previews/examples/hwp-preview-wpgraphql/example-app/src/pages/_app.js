import Layout from "@/components/Layout";
import "@/styles/globals.css";
import { ApolloProvider } from "@apollo/client";
import { client } from "../lib/client";

export default function App({ Component, pageProps }) {
  return (
    // ApolloProvider makes the Apollo Client available to the rest of the app
    <ApolloProvider client={client}>
      <Layout>
        <Component {...pageProps} />
      </Layout>
    </ApolloProvider>
  );
}
