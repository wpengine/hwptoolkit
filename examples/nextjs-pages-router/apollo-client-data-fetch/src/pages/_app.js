import "@/styles/globals.css";
import { ApolloClient, ApolloProvider, InMemoryCache } from "@apollo/client";

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

const client = new ApolloClient({
  uri: WORDPRESS_URL + "/graphql",
  cache: new InMemoryCache(),
});

export default function App({ Component, pageProps }) {
  return (
    <ApolloProvider client={client}>
      <Component {...pageProps} />
    </ApolloProvider>
  );
}
