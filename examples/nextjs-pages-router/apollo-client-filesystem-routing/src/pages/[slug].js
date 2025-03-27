import { gql } from "@apollo/client";
import getApolloClient from "@/lib/getApolloClient";

export default function Page({ page }) {
  if (!page) {
    return <div>Page not found</div>;
  }

  return (
    <div className="container mx-auto py-12">
      <h1 className="text-4xl font-semibold text-gray-800 mb-6">
        {page.title}
      </h1>
      <div
        className="prose"
        dangerouslySetInnerHTML={{ __html: page.content }}
      />
    </div>
  );
}

export async function getStaticPaths() {
  const client = getApolloClient();
  const { data } = await client.query({
    query: gql`
      query GetPages {
        pages {
          nodes {
            slug
          }
        }
      }
    `,
  });

  const paths = data.pages.nodes.map((page) => ({
    params: { slug: page.slug },
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
      query GetPageBySlug($slug: ID!) {
        page(id: $slug, idType: URI) {
          title
          content
        }
      }
    `,
    variables: { slug },
  });
  console.debug('data', data);
  const page = data.page;

  return {
    props: {
      page,
    },
  };
}
