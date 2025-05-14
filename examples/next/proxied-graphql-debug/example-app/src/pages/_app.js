import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import getApolloClient from "@/lib/client";
import "@/styles/globals.css";

export default function App({ Component, pageProps }) {
  const router = useRouter();
  const client = getApolloClient();
  return (
    <ApolloProvider client={client}>
      <Component {...pageProps} key={router.asPath} />
    </ApolloProvider>
  );
}