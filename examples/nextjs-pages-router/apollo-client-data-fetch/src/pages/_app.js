import "@/styles/globals.css";
import { client } from "@/lib/client";
import { ApolloProvider } from "@apollo/client";

export default function App({ Component, pageProps }) {
  return (
    <ApolloProvider client={client}>
      <main className='bg-stone-100 text-gray-800 p-16 min-h-screen'>
        <Component {...pageProps} />
      </main>
    </ApolloProvider>
  );
}
