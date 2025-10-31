import { gql } from "@apollo/client";
import { useRouteData } from "@/lib/templates/context";
import ProductCard from "@/components/Products/ProductCard";
import React from "react";
import { PRODUCTS_QUERY } from "@/lib/graphQL/productGraphQL";
export default function RecentProducts({
	count = 12,
	columns = {
		desktop: 4,
		tablet: 3,
		mobile: 2,
	},
	title = "Recent Products",
	showTitle = true,
	displayType = "recent", // recent, bestsellers, expensive, sale, rated
	customQuery = null, // Allow custom query override
}) {
	const { graphqlData } = useRouteData();

	const getProductData = () => {
		switch (displayType) {
			case "bestsellers":
				return graphqlData?.ProductDisplayQueries.BestSellers || [];
			case "expensive":
				return graphqlData?.ProductDisplayQueries.MostExpensive || [];
			case "sale":
				return graphqlData?.ProductDisplayQueries.OnSale || [];
			case "rated":
				return graphqlData?.ProductDisplayQueries.BestRated || [];
			case "recent":
			default:
				return graphqlData?.ProductDisplayQueries.RecentProducts || [];
		}
	};

	const products = getProductData();

	const displayProducts = products.nodes ? products.nodes.slice(0, count) : [];

	const hasError = () => {
		const errorKey = {
			recent: "RecentProducts",
			bestsellers: "BestSellers",
			expensive: "MostExpensive",
			sale: "OnSale",
			rated: "BestRated",
		}[displayType];

		return graphqlData?.[errorKey]?.error;
	};

	if (hasError()) {
		console.error(`Error fetching ${displayType} products:`, hasError());
		return <div>Error loading {displayType} products.</div>;
	}

	if (!products || products.length === 0) {
		return <div>No {displayType} products found.</div>;
	}

	const getGridColumns = (cols) => `repeat(${cols}, 1fr)`;

	return (
		<div className="recent-products">
			{showTitle && <h2>{title}</h2>}
			<div className="products-grid">
				{" "}
				{(() => {
					const productCards = [];
					for (let i = 0; i < displayProducts.length; i++) {
						const product = displayProducts[i];
						productCards.push(<ProductCard key={product.id} product={product} />);
					}
					return productCards;
				})()}
			</div>

			<style jsx>{`
				.recent-products {
					margin: 40px 0;
				}

				.recent-products h2 {
					font-size: 28px;
					margin-bottom: 24px;
					color: #2c3e50;
					text-align: center;
				}

				.products-grid {
					display: grid;
					grid-template-columns: ${getGridColumns(columns.desktop)};
					gap: 20px;
					padding: 0;
				}

				@media (max-width: 1024px) {
					.products-grid {
						grid-template-columns: ${getGridColumns(columns.tablet)};
					}
				}

				@media (max-width: 768px) {
					.products-grid {
						grid-template-columns: ${getGridColumns(columns.mobile)};
						gap: 15px;
					}

					.recent-products h2 {
						font-size: 24px;
						margin-bottom: 20px;
					}
				}
			`}</style>
		</div>
	);
}

RecentProducts.query = {
	query: PRODUCTS_QUERY
};