import { ApolloProvider } from "@apollo/client";
import { useRouter } from "next/router";
import { AuthProvider } from "@/lib/auth/AuthProvider";
import getApolloClient from "@/lib/getApolloClient";
import "@/styles/globals.css";

export default function App({ Component, pageProps }) {
  const router = useRouter();
  const client = getApolloClient();
  return (
    <ApolloProvider client={client}>
      <AuthProvider>
        <Component {...pageProps} key={router.asPath} />
      </AuthProvider>
    </ApolloProvider>
  );
}
