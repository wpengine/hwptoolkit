import Products from "@/components/Products/Products";
export default function TaxonomyProductCat({ uri, graphqlData, templateData }) {
  
	const getTaxonomySlug = (uri) => {
		const matches = uri.match(/\/product-category\/(?:.*\/)?([^\/]+)\/?$/);
		return matches ? matches[1] : null;
	};

	const categorySlugFormatted = getTaxonomySlug(uri)
		? getTaxonomySlug(uri)
				.replace(/-/g, " ")
				.replace(/\b\w/g, (char) => char.toUpperCase())
		: null;
	return (
		<>
			<Products
				title={`${categorySlugFormatted}`}
				showTitle={true}
				displayType="recent"
				count={24}
				columns={{
					desktop: 4,
					tablet: 3,
					mobile: 2,
				}}
			/>
		</>
	);
}

TaxonomyProductCat.queries = [
	{
		name: "ProductDisplayQueries",
		query: Products.query.query,
		variables: (event, { uri }, graphqlData, templateData) => {
			let categorySlug = "";
			if (uri) {
				const matches = uri.match(/\/product-category\/(?:.*\/)?([^\/]+)\/?$/);
				categorySlug = matches ? matches[1] : null;
			}
			return Products.query.variables({
				displayType: "recent",
				categoryIn: categorySlug ? [categorySlug] : null,
				count: 50,
			});
		},
	},
];
