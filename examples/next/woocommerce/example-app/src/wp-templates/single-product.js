import SingleProduct from "@/components/Products/SingleProduct";

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
		query: /* GraphQL */ `
			query SingleProductQuery($id: ID!) {
				product(id: $id, idType: SLUG) {
					id
					databaseId
					name
					slug
					uri
					description
					shortDescription
					sku
					date
					onSale
					productCategories {
						nodes {
							id
							name
							slug
						}
					}
					productTags {
						nodes {
							id
							name
							slug
						}
					}
					image {
						id
						sourceUrl
						altText
						mediaDetails {
							width
							height
						}
					}
					galleryImages {
						nodes {
							id
							sourceUrl
							altText
							mediaDetails {
								width
								height
							}
						}
					}

					... on SimpleProduct {
						price
						regularPrice
						salePrice
						stockStatus
						stockQuantity
						weight
						length
						width
						height
					}

					... on VariableProduct {
						price
						regularPrice
						salePrice
						stockStatus
						stockQuantity
						weight
						length
						width
						height
						variations {
							nodes {
								id
								name
								price
								regularPrice
								salePrice
								stockStatus
								stockQuantity
							}
						}
					}

					... on ExternalProduct {
						price
						regularPrice
						salePrice
						externalUrl
						buttonText
					}

					... on GroupProduct {
						price
						regularPrice
						salePrice
						products {
							nodes {
								id
								name
								slug
							}
						}
					}
				}
			}
		`,
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
