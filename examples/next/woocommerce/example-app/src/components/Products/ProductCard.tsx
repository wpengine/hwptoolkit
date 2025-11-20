import React from "react";
import Link from "next/link";
import Image from "next/image";

import { Product } from "@/interfaces/product.interface";
import ProductQuantity from "./Quantity";
import AddToCart from "./AddToCart";
import ProductPrice from "./Price";
import ProductVariations from "./Variations";
import ProductRating from "./Rating";

export default function ProductCard({ product }: { product: Product }) {
	if (!product) {
		return null;
	}

	const productImage = product.image?.sourceUrl || "/placeholder-product.jpg";
	const productAlt = product.image?.altText || product.name || "Product image";

	const productPrices = {
		onSale: product.onSale,
		price: product.price,
		regularPrice: product.regularPrice,
		salePrice: product.salePrice
	}
	return (
		<div className="product-card">
			<Link href={`/product/${product.slug}`} className="product-link">
				<div className="product-image-container">
					<Image
						src={productImage}
						alt={productAlt}
						width={600}
						height={600}
						className="product-image"
						placeholder="blur"
						blurDataURL="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k="
					/>

					{/* Sale Badge */}
					{product.onSale && <span className="sale-badge">Sale</span>}

					{/* Stock Status */}
					{product.stockStatus === "OUT_OF_STOCK" && <span className="out-of-stock-badge">Out of Stock</span>}
				</div>

				{/* Product Info */}
				<div className="product-info">
					<h3 className="product-title">{product.name}</h3>
					
					{/* Price */}
					<ProductPrice prices={productPrices} size={"small"} />

					{/* Rating */}
					<ProductRating product={{ averageRating: product.averageRating, reviewCount: product.reviewCount }} />
				</div>
			</Link>

			{/* Add to Cart Button */}
			<AddToCart product={product} card={true} />

			<style jsx>{`
				.product-card {
					border: 1px solid #e1e5e9;
					border-radius: 8px;
					overflow: hidden;
					transition: transform 0.2s ease, box-shadow 0.2s ease;
					background: white;
					display: flex;
					flex-direction: column;
					height: 100%;
					text-align: center;
					padding-bottom: 1.5rem;
				}

				.product-card:hover {
					transform: translateY(-2px);
					box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
				}

				.product-link {
					text-decoration: none;
					color: inherit;
					flex: 1;
					display: flex;
					flex-direction: column;
				}

				.product-image-container {
					position: relative;
					width: 100%;
					overflow: hidden;
				}

				.product-image {
					width: 100%;
					height: 100%;
					object-fit: cover;
					transition: transform 0.3s ease;
				}

				.product-card:hover .product-image {
					transform: scale(1.05);
				}

				.sale-badge {
					position: absolute;
					top: 10px;
					left: 10px;
					background: #e74c3c;
					color: white;
					padding: 4px 8px;
					border-radius: 4px;
					font-size: 12px;
					font-weight: bold;
					z-index: 2;
				}

				.out-of-stock-badge {
					position: absolute;
					top: 10px;
					right: 10px;
					background: red;
					color: white;
					padding: 4px 8px;
					border-radius: 4px;
					font-size: 12px;
					font-weight: bold;
					z-index: 2;
				}

				.product-info {
					padding: 16px;
					flex: 1;
					display: flex;
					flex-direction: column;
				}

				.product-title {
					font-size: 16px;
					font-weight: 600;
					margin: 0 0 8px 0;
					line-height: 1.4;
					color: #2c3e50;
				}

				.product-price {
					margin-bottom: 8px;
				}

				.price {
					font-size: 18px;
					font-weight: bold;
					color: #27ae60;
				}

				.sale-price {
					font-size: 18px;
					font-weight: bold;
					color: #e74c3c;
					margin-right: 8px;
				}

				.regular-price {
					font-size: 14px;
					color: #7f8c8d;
					text-decoration: line-through;
				}

				.product-rating {
					display: flex;
					align-items: center;
					gap: 4px;
					margin-top: auto;
				}

				.stars {
					color: #f39c12;
					font-size: 14px;
				}

				.rating-count {
					font-size: 12px;
					color: #7f8c8d;
				}

				.product-actions {
					padding: 0 16px 16px 16px;
				}

				.add-to-cart-btn {
					width: 100%;
					padding: 12px;
					background: #3498db;
					color: white;
					border: none;
					border-radius: 4px;
					font-size: 14px;
					font-weight: 600;
					cursor: pointer;
					transition: all 0.2s ease;
				}

				.add-to-cart-btn:hover:not(:disabled) {
					background: #2980b9;
				}

				.add-to-cart-btn:disabled {
					background: #bdc3c7;
					cursor: not-allowed;
				}

				.add-to-cart-btn.loading {
					background: #f39c12;
					cursor: wait;
				}

				.add-to-cart-btn.added {
					background: #27ae60;
				}
			
				@media (max-width: 768px) {
					.product-title {
						font-size: 14px;
					}

					.product-info {
						padding: 12px;
					}
				}
			`}</style>
		</div>
	);
}
