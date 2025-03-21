import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";
import Link from "next/link";

export default function MoviesPage({ movies }) {
  if (!movies.length) {
    return (
      <div className="flex items-center justify-center h-screen text-xl text-gray-600">
        No movies found.
      </div>
    );
  }

  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">Movies</h1>
      <div className="space-y-4">
        {movies.map((movie) => (
          <div key={movie.slug} className="py-2">
            <Link
              href={movie.uri}
              className="text-xl font-medium text-indigo-600 hover:text-indigo-800"
            >
              {movie.title}
            </Link>
          </div>
        ))}
      </div>
    </div>
  );
}

export async function getStaticProps() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetMovies {
        movies {
          nodes {
            title
            slug
            uri
          }
        }
      }
    `,
  });

  const movies = data.movies.nodes;

  return {
    props: {
      movies,
    },
  };
}
