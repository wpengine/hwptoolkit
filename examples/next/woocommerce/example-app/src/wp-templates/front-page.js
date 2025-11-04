import Products from "@/components/Products/Products";

export default function FrontPage({ graphqlData }) {
	console.log("FrontPage graphqlData:", graphqlData);

	return (
		<div>
			<Products
				count={4}
				columns={{ desktop: 4, tablet: 2, mobile: 1 }}
				title="Featured Products"
				displayType="featured"
				queryName="FeaturedQuery"
			/>

			<Products
				count={4}
				columns={{ desktop: 4, tablet: 2, mobile: 1 }}
				title="Top Rated"
				displayType="rated"
				queryName="RatedQuery"
			/>

			<Products
				count={3}
				columns={{ desktop: 3, tablet: 2, mobile: 1 }}
				title="Special Offers"
				displayType="sale"
				queryName="SaleQuery" 
			/>

			<Products
				count={4}
				title="New Arrivals"
				displayType="recent"
				queryName="RecentQuery"
			/>
		</div>
	);
}

FrontPage.queries = [
	{
		name: "FeaturedQuery",
		query: Products.query.query,
		variables: () => {
			return Products.query.variables({
				displayType: "featured",
				categoryIn: null,
				count: 4,
				featured: true,
			});
		},
	},
	{
		name: "RatedQuery",
		query: Products.query.query,
		variables: () => {
			return Products.query.variables({
				displayType: "rated",
				categoryIn: null,
				count: 4,
				rating: [3, 4, 5],
			});
		},
	},
	{
		name: "SaleQuery",
		query: Products.query.query,
		variables: () => {
			return Products.query.variables({
				displayType: "sale",
				categoryIn: null,
				count: 4,
			});
		},
	},
	{
		name: "RecentQuery",
		query: Products.query.query,
		variables: () => {
			return Products.query.variables({
				displayType: "recent",
				categoryIn: null,
				count: 4,
			});
		},
	},
];
