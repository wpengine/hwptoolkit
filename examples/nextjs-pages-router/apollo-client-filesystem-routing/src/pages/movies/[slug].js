import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";

export default function MoviePage({ movie }) {
  if (!movie) {
    return (
      <div className="container mx-auto py-12">
        <h1 className="text-4xl font-semibold text-gray-800 mb-6">
          Movie Not Found
        </h1>
        <p className="text-lg text-gray-600">
          The movie you are looking for does not exist.
        </p>
      </div>
    );
  }

  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">
        {movie.title}
      </h1>
      <div className="prose lg:prose-xl text-gray-800">
        <div dangerouslySetInnerHTML={{ __html: movie.content }} />
      </div>
    </div>
  );
}

export async function getStaticPaths() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetMovieSlugs {
        movies {
          nodes {
            slug
          }
        }
      }
    `,
  });

  const paths = data.movies.nodes.map((movie) => ({
    params: { slug: movie.slug },
  }));

  return {
    paths,
    fallback: "blocking",
  };
}

export async function getStaticProps({ params }) {
  const client = getApolloClient();
  const { slug } = params;

  const { data } = await client.query({
    query: gql`
      query GetMovieBySlug($slug: ID!) {
        movie(id: $slug, idType: SLUG) {
          title
          content
        }
      }
    `,
    variables: { slug },
  });

  const movie = data.movie;

  return {
    props: {
      movie,
    },
  };
}
