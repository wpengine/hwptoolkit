export default function SingleTemplate({ graphqlData }) {
  const { SinglePostQuery } = graphqlData;
  return (
    <>
      <div id="single-template">
        <h2>{SinglePostQuery.post.title}</h2>
        <div
          dangerouslySetInnerHTML={{
            __html: SinglePostQuery.post.content,
          }}
        />
      </div>
    </>
  );
}

// export const queries = "test";

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
