import { gql } from "@apollo/client";
import { useRouteData } from "@/lib/providers/RouterProvider";
import ProductCard from "@/components/Products/ProductCard";
import React from "react";
import { PRODUCTS_QUERY } from "@/lib/graphQL/productGraphQL";

interface ProductsProps {
	count?: number;
	columns?: {
		desktop: number;
		tablet: number;
		mobile: number;
	};
	title?: string;
	showTitle?: boolean;
	displayType?: "recent" | "featured" | "expensive" | "sale" | "rated";
	categoryIn?: string[];
	featured?: boolean;
	queryName?: string;
}

export default function Products({
	count = 12,
	columns = {
		desktop: 4,
		tablet: 3,
		mobile: 2,
	},
	title = "Recent Products",
	showTitle = true,
	displayType = "recent",
	categoryIn = [],
	queryName = "ProductDisplayQueries", // ✅ Default query name
}: ProductsProps) {
	const { graphqlData } = useRouteData();

	const products = graphqlData?.[queryName]?.products?.nodes || [];
	const displayProducts = products.slice(0, count);

	if (!products || products.length === 0) {
		return (
			<div className="text-center py-12">
				<p className="text-gray-500">No {displayType} products found.</p>
			</div>
		);
	}

	const getGridColumns = (cols: number) => `repeat(${cols}, 1fr)`;

	return (
		<div className="recent-products">
			{showTitle && <h2>{title}</h2>}
			<div className="products-grid">
				{displayProducts.map((product) => (
					<ProductCard key={product.id} product={product} />
				))}
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

Products.query = {
	query: PRODUCTS_QUERY,
	variables: ({ displayType = "recent", categoryIn = null, count = 50, rating = [] }) => {
		const orderByConfig = {
			recent: { field: "DATE", order: "DESC" },
			featured: { order: "DESC", featured: true },
			expensive: { field: "PRICE", order: "DESC" },
			sale: { field: "PRICE", order: "DESC", onSale: true },
			rated: { field: "RATING", order: "ASC", rating },
		};

		const config = orderByConfig[displayType] || orderByConfig.recent;

		return {
			categoryIn: categoryIn && categoryIn.length > 0 ? categoryIn : null,
			first: count,
			orderByField: config.field,
			orderByOrder: config.order,
			onSale: config.onSale || null,
			featured: config.featured || null,
			rating: config.rating || null,
		};
	},
};
