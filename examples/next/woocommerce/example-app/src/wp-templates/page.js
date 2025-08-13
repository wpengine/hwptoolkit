export default function Page({ graphqlData }) {
  const { SinglePageQuery } = graphqlData;
  return (
    <>
      <div id="single-template">
        <h2>{SinglePageQuery.page.title}</h2>
        <div
          dangerouslySetInnerHTML={{
            __html: SinglePageQuery.page.content,
          }}
        />
      </div>
    </>
  );
}

Page.queries = [
  {
    name: "SinglePageQuery",
    query: /* GraphQL */ `
      query SinglePageQuery($id: ID!) {
        page(id: $id, idType: URI) {
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
