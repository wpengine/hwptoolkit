export default function Rating({ product }: { product: { averageRating: number; reviewCount: number } }) {
	return (
		<>
			{product.averageRating > 0 && (
				<div className="product-rating">
					<span className="stars">
						{"★".repeat(Math.floor(product.averageRating))}
						{"☆".repeat(5 - Math.floor(product.averageRating))}
					</span>
					<span className="rating-count">({product.reviewCount})</span>
				</div>
			)}
		</>
	);
}
