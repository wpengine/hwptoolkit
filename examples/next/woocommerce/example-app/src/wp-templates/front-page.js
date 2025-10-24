import { useAuth } from "@/lib/auth/AuthProvider";
import { gql } from "@apollo/client";
import Products from "@/components/Products/Products";

export default function FrontPage() {

	return (
		<div>
			<Products
				count={3}
				columns={{ desktop: 3, tablet: 2, mobile: 1 }}
				title="Featured Products"
				displayType="bestsellers"
			/>

			<Products
				count={4}
				columns={{ desktop: 4, tablet: 2, mobile: 1 }}
				title="Customer Favorites"
				displayType="rated"
			/>

			<Products count={3} columns={{ desktop: 3, tablet: 2, mobile: 1 }} title="Special Offers" displayType="sale" />

			<Products count={4} title="New Arrivals" displayType="recent" />
		</div>
	);
}
FrontPage.queries = [
	Products.query, // Ensure Products query is included
];
