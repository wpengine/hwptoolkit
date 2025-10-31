import React from "react";
import Image from "next/image";

import { Product } from "@/interfaces/product.interface";

import ProductPrice from "./Price";

export default function ProductVariations({ variations }: { variations: Product["variations"] }) {
	if (!variations) {
		return null;
	}
	return (
		<div className="product-variations">
			<h3>Variations:</h3>
			{variations.nodes.map((variation) => {
				const currentVariationPrice = {
					onSale: variation.onSale,
					price: variation.price,
					regularPrice: variation.regularPrice,
					salePrice: variation.salePrice,
				};
				console.log("varprices", currentVariationPrice);
				return (
					<div key={variation.id} className="variation">
						{variation.image && (
							<Image
								src={variation.image.sourceUrl}
								alt={variation.image.altText}
								width={100}
								height={100}
								className="variation-image"
							/>
						)}
						<div className="variation-info">
							<h4 className="variation-title">{variation.name}</h4>
							<ProductPrice prices={currentVariationPrice} size="small" />
						</div>
					</div>
				);
			})}
			<style jsx>{`
				.product-variations {
					margin-top: 2rem;
				}
				.variation {
					display: flex;
					align-items: center;
					margin-bottom: 1rem;
					padding: 1rem;
					border: 1px solid #e5e7eb;
					border-radius: 0.5rem;
				}
				.variation-image {
					width: 100px;
					height: 100px;
					object-fit: cover;
					margin-right: 1rem;
					border-radius: 0.375rem;
				}
				.variation-info {
					flex: 1;
				}
				.variation-title {
					font-size: 1rem;
					font-weight: 600;
					margin: 0 0 0.5rem 0;
				}
			`}</style>
		</div>
	);
}
