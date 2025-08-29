import SinglePost from "@/components/Posts/SinglePost/SinglePost";

export default function Single({ graphqlData }) {
  const { SinglePostQuery } = graphqlData;
  return (
    <>
      <SinglePost post={SinglePostQuery.post} />
    </>
  );
}

Single.queries = [
  {
    name: "SinglePostQuery",
    query: `
      query GetPost($id: ID!) {
        post(id: $id, idType: URI) {
          id
          databaseId
          title
          date
          content
          commentCount
          categories {
            nodes {
              name
              slug
              uri
            }
          }
          tags {
            nodes {
              name
              slug
              uri
            }
          }
          author {
            node {
              name
              avatar {
                url
              }
            }
          }
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
        }
      }
    `,
    variables: (event, { uri }) => ({
      id: uri,
    }),
  },
];
