import SinglePage from "@/components/Pages/SinglePage";
export default function Page({ graphqlData }) {
	const { SinglePageQuery } = graphqlData;
	return (
		<>
			<SinglePage page={SinglePageQuery.page} />
		</>
	);
}

Page.queries = [
	{
		name: "SinglePageQuery",
		query: `
			query SinglePageQuery($id: ID!) {
				page(id: $id, idType: URI) {
					 id
          databaseId
          title
          date
          content
          commentCount
				}
			}
		`,
		variables: (event, { uri }) => ({
			id: uri,
		}),
	},
];
