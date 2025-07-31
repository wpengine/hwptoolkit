import Layout from "@/components/Layout";

export default function SingleTemplate({ graphqlData }) {
  const { SinglePostQuery } = graphqlData;
  return (
    <Layout>
      <h2>{SinglePostQuery.response.data.post.title}</h2>
      <div
        dangerouslySetInnerHTML={{
          __html: SinglePostQuery.response.data.post.content,
        }}
      />
    </Layout>
  );
}

export const queries = "test";

SingleTemplate.queries = [
  {
    name: "SinglePostQuery",
    query: /* GraphQL */ `
      query SinglePostQuery($id: ID!) {
        post(id: $id, idType: URI) {
          id
          title
          content
          date
        }
      }
    `,
    variables: (event, { uri }) => ({
      id: uri,
    }),
  },
];
