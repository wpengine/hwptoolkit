import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";
import Link from "next/link";

export default function Home({ posts, movies }) {
  return (
    <div className="grid grid-rows-[20px_1fr_20px] items-center justify-items-center min-h-screen p-8 pb-20 gap-16 sm:p-20 font-[family-name:var(--font-geist-sans)]">
      <main className="flex flex-col gap-[32px] row-start-2 items-center sm:items-start">
        <section className="w-full">
          <h2 className="text-2xl font-semibold text-gray-800 mb-6">
            Latest Posts
          </h2>
          <ul className="space-y-4">
            {posts.map((post) => (
              <li key={post.slug} className="py-4">
                <Link href={post.uri}>{post.title}</Link>
                <div className="mt-2 text-sm text-gray-600" dangerouslySetInnerHTML={{ __html: post.excerpt }}></div>

              </li>
            ))}
          </ul>
        </section>

        <section className="w-full mt-12">
          <h2 className="text-2xl font-semibold text-gray-800 mb-6">
            Latest Movies
          </h2>
          <ul className="space-y-4">
            {movies.map((movie) => (
              <li key={movie.slug} className="py-4">
                <Link href={movie.uri}>{movie.title}</Link>
                <div className="mt-2 text-sm text-gray-600" dangerouslySetInnerHTML={{ __html: movie.excerpt }}></div>
              </li>
            ))}
          </ul>
        </section>
      </main>
      <footer className="row-start-3 flex gap-[24px] flex-wrap items-center justify-center">
        <a
          className="flex items-center gap-2 hover:underline hover:underline-offset-4"
          href="https://nextjs.org/learn"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn
        </a>
        <a
          className="flex items-center gap-2 hover:underline hover:underline-offset-4"
          href="https://vercel.com/templates?framework=next.js"
          target="_blank"
          rel="noopener noreferrer"
        >
          Examples
        </a>
        <a
          className="flex items-center gap-2 hover:underline hover:underline-offset-4"
          href="https://nextjs.org"
          target="_blank"
          rel="noopener noreferrer"
        >
          Go to nextjs.org →
        </a>
      </footer>
    </div>
  );
}

// Fetch latest posts and movies data
export async function getStaticProps() {
  const client = getApolloClient();

  const { data } = await client.query({
    query: gql`
      query GetLatestPostsAndMovies {
        posts(first: 5) {
          nodes {
            title
            slug
            excerpt
            uri
          }
        }
        movies(first: 5) {
          nodes {
            title
            slug
            uri
          }
        }
      }
    `,
  });

  return {
    props: {
      posts: data.posts.nodes,
      movies: data.movies.nodes,
    },
  };
}
