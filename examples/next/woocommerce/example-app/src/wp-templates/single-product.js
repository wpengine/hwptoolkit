import SingleProduct from "@/components/Products/SingleProduct";
import { SINGLE_PRODUCT_QUERY } from "@/lib/graphQL/productGraphQL";
export default function SingleProductPage({ graphqlData }) {
	const { SingleProductQuery } = graphqlData;

	if (!SingleProductQuery || !SingleProductQuery.product) {
		return <div>Product not found</div>;
	}

	return (
		<>
			<SingleProduct product={SingleProductQuery.product} />
		</>
	);
}

SingleProductPage.queries = [
	{
		name: "SingleProductQuery",
		query: SINGLE_PRODUCT_QUERY,		
		variables: (event, { uri }) => {
			const slug = uri
				.replace(/^\/+|\/+$/g, "")
				.split("/")
				.pop();
			return {
				id: slug,
			};
		},
	},
];
