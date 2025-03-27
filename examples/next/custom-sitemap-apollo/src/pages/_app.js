import "@/styles/globals.css";
import { client } from "@/lib/client";
import { ApolloProvider } from "@apollo/client";

export default function App({ Component, pageProps }) {
  return (
    // ApolloProvider makes the Apollo Client available to the rest of the app
    <ApolloProvider client={client}>
      <Component {...pageProps} />
    </ApolloProvider>
  );
}
