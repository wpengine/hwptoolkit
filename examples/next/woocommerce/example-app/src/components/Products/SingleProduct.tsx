import React, { useState, useEffect } from "react";
import Image from "next/image";
import { Product } from "@/interfaces/product.interface";
import { RELATED_PRODUCTS_QUERY } from "@/lib/graphQL/productGraphQL";
import { useQuery } from "@apollo/client/react/hooks/useQuery";
import ProductCard from "./ProductCard";
import ProductPrice from "./Price";
import AddToCart from "./AddToCart";
import ProductQuantity from "./Quantity";
import ProductVariations from "./Variations";

interface SingleProductProps {
	product: Product;
	relatedProducts?: Product[];
}

export default function SingleProduct({ product }: SingleProductProps) {
	const [activeTab, setActiveTab] = useState<string>("description");
	const [quantity, setQuantity] = useState<number>(1);
	const [selectedVariation, setSelectedVariation] = useState<Product["variations"]["nodes"][0] | null>(null);
	// ✅ Store selected attributes as array of objects
	const [selectedAttributes, setSelectedAttributes] = useState<{ attributeName: string; attributeValue: string }[]>([]);

	if (!product) {
		return null;
	}

	const productPrices = {
		onSale: product.onSale,
		price: product.price,
		regularPrice: product.regularPrice,
		salePrice: product.salePrice,
	};

	const displayImage = selectedVariation?.image || product.image;

	const handleAttributeSelect = (attributeName: string, attributeValue: string) => {
		setSelectedAttributes((prev) => {
			// Remove any existing selection for this attribute
			const filtered = prev.filter((attr) => attr.attributeName !== attributeName);
			// Add the new selection
			return [...filtered, { attributeName, attributeValue }];
		});
	};

	useEffect(() => {
		if (!product.variations?.nodes || selectedAttributes.length === 0) {
			setSelectedVariation(null);
			return;
		}

		if (selectedVariation?.attributes?.nodes) {
			const currentStillMatches = selectedAttributes.every((selectedAttr) => {
				const varAttr = selectedVariation.attributes.nodes.find((attr) => attr.name === selectedAttr.attributeName);

				if (!varAttr || !varAttr.value || varAttr.value.trim() === "") {
					return true; // Keep current variation
				}

				// Check if value matches
				return varAttr.value.toLowerCase() === selectedAttr.attributeValue.toLowerCase();
			});

			if (currentStillMatches) {
				
				return;
			}
		}
		const matchingVariation = product.variations.nodes.find((variation) => {
			if (!variation.attributes?.nodes) return false;

			// Check if all selected attributes match this variation
			return selectedAttributes.every((selectedAttr) => {
				const varAttr = variation.attributes.nodes.find((attr) => attr.name === selectedAttr.attributeName);
				if (!varAttr || !varAttr.value || varAttr.value.trim() === "") {
					return true;
				}

				// Compare values (case-insensitive)
				return varAttr.value.toLowerCase() === selectedAttr.attributeValue.toLowerCase();
			});
		});

		setSelectedVariation(matchingVariation || null);
	}, [selectedAttributes, product.variations, selectedVariation]);

	// Related Products
	const getRelatedProducts = () => {
		const categorySlugs = product.productCategories?.nodes?.map((cat) => cat.slug) || [];
		const { data: relatedData } = useQuery(RELATED_PRODUCTS_QUERY, {
			variables: {
				categoryIn: categorySlugs,
				exclude: [product.databaseId],
			},
			skip: categorySlugs.length === 0,
		});
		return relatedData?.products?.nodes || [];
	};

	const relatedProducts = getRelatedProducts();
	
	return (
		<div className="container mx-auto px-4 py-8">
			<div className="grid lg:grid-cols-2 gap-12 mb-12">
				<div className="space-y-4">
					<div className="relative aspect-square bg-gray-100 rounded-lg overflow-hidden">
						{product.onSale && (
							<span className="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-md z-10">
								On Sale
							</span>
						)}
						{displayImage ? (
							<Image
								src={displayImage.sourceUrl}
								alt={displayImage.altText || product.name}
								fill
								className="object-cover"
								priority
							/>
						) : (
							<div className="flex items-center justify-center h-full text-gray-400">
								<svg className="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										strokeWidth={1}
										d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
									/>
								</svg>
							</div>
						)}
					</div>
				</div>

				{/* Product Info */}
				<div className="space-y-6">
					<div>
						{product.productCategories?.nodes && product.productCategories.nodes.length > 0 && (
							<p className="text-sm text-gray-600 mb-2">
								{product.productCategories.nodes.map((cat) => cat.name).join(", ")}
							</p>
						)}
						<h1 className="text-3xl font-bold mb-2">{product.name}</h1>
						{product.shortDescription && (
							<div className="text-gray-600 mb-4" dangerouslySetInnerHTML={{ __html: product.shortDescription }} />
						)}
						<p className="text-sm text-gray-400">
							{product.sku && <span className="text-gray-600">SKU: {product.sku}</span>}
						</p>

						{/* ✅ Pass attribute selection handler */}
						<ProductVariations
							variations={product.variations}
							globalAttributes={product.globalAttributes}
							selectedVariation={selectedVariation}
							onVariationSelect={setSelectedVariation}
							onAttributeSelect={handleAttributeSelect}
							selectedAttributes={selectedAttributes}
						/>

						<ProductPrice prices={productPrices} />
					</div>

					<div className="add-to-cart-container flex items-center gap-4">
						<ProductQuantity product={product} quantity={quantity} setQuantity={setQuantity} />

						{/* ✅ Pass selectedAttributes array */}
						<AddToCart
							product={product}
							quantity={quantity}
							variation={selectedAttributes}
							variationId={selectedVariation?.databaseId}
						/>
					</div>
				</div>
			</div>

			{/* Product Tabs */}
			<div className="product-tabs mb-12">
				<div className="border-b mb-6">
					<button
						className={`py-2 px-4 mr-4 ${
							activeTab === "description" ? "border-b-2 border-blue-600 font-semibold" : "text-gray-600"
						}`}
						onClick={() => setActiveTab("description")}
					>
						Description
					</button>
					<button
						className={`py-2 px-4 mr-4 ${
							activeTab === "additionalInfo" ? "border-b-2 border-blue-600 font-semibold" : "text-gray-600"
						}`}
						onClick={() => setActiveTab("additionalInfo")}
					>
						Additional Information
					</button>
					<button
						className={`py-2 px-4 ${
							activeTab === "reviews" ? "border-b-2 border-blue-600 font-semibold" : "text-gray-600"
						}`}
						onClick={() => setActiveTab("reviews")}
					>
						Reviews ({product.reviewCount || 0})
					</button>
				</div>
				<div className="tab-content">
					{activeTab === "description" && (
						<div
							className="prose max-w-none"
							dangerouslySetInnerHTML={{ __html: product.description || "<p>No description available.</p>" }}
						/>
					)}
					{activeTab === "additionalInfo" && (
						<div>
							{product.attributes?.nodes && product.attributes.nodes.length > 0 ? (
								<table className="w-full table-auto border-collapse">
									<tbody>
										{product.attributes.nodes.map((attr) => (
											<tr key={attr.id} className="border-b">
												<td className="py-2 px-4 font-medium">{attr.name}</td>
												<td className="py-2 px-4">{attr.options?.join(", ") || "N/A"}</td>
											</tr>
										))}
									</tbody>
								</table>
							) : (
								<p>No additional information available.</p>
							)}
						</div>
					)}
					{activeTab === "reviews" && (
						<div>
							{product.reviewCount && product.reviewCount > 0 ? (
								<p>Reviews would be displayed here.</p>
							) : (
								<p>No reviews yet.</p>
							)}
						</div>
					)}
				</div>
			</div>

			{/* Related Products */}
			{relatedProducts.length > 0 && (
				<div className="related-products">
					<h2 className="text-2xl font-bold mb-6">Related Products</h2>
					<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
						{relatedProducts.map((relatedProduct: Product) => (
							<ProductCard key={relatedProduct.databaseId} product={relatedProduct} />
						))}
					</div>
				</div>
			)}
		</div>
	);
}
