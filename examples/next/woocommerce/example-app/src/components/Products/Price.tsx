import type { ProductPrices, ProductPriceProps } from "@/interfaces/product.interface";

export default function ProductPrice({ prices, size }: ProductPriceProps) {
	const getFontSize = () => {
		switch (size) {
			case "small":
				return "0.875rem"; // 14px
			case "large":
				return "1.5rem"; // 24px
			default:
				return "1.25rem"; // 20px
		}
	};
	return (
		<div className="product-price">
			{prices.onSale ? (
				<>
					<span className="sale-price">{prices.salePrice}</span>
					<span className="regular-price">{prices.regularPrice}</span>
				</>
			) : (
				<span className="price">{prices.price}</span>
			)}
			{(!prices.regularPrice && prices.salePrice) ?? <span className="price">{prices.price}</span>}
			<style jsx>{`
				.product-price {
					font-size: ${getFontSize()};
					margin-top: 0.5rem;
				}
				.sale-price {
					color: #e74c3c;
					margin-right: 0.5rem;
				}
				.regular-price {
					text-decoration: line-through;
					color: #7f8c8d;
				}
				.product-card .product-price {
					font-size: 0.85rem;
				}
			`}</style>
		</div>
	);
}
