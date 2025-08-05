import "@/styles/globals.css";
import { client } from "@/lib/client";
import { ApolloProvider } from "@apollo/client";
import Header from "@/components/Header";

export default function App({ Component, pageProps }) {
  return (
    // ApolloProvider makes the Apollo Client available to the rest of the app
    <ApolloProvider client={client}>
      <main className='bg-stone-100 text-gray-800 pb-16 min-h-screen'>
        <Header />

        <Component {...pageProps} />
      </main>
    </ApolloProvider>
  );
}