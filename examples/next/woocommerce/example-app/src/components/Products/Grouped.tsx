import React, { useState } from "react";
import Image from "next/image";
import { Product } from "@/interfaces/product.interface";
import ProductPrice from "./Price";
import { useCart } from "@/lib/providers/CartProvider";

interface ProductGroupProps {
	products: Product["products"];
}

export default function ProductGroup({ products }: ProductGroupProps) {
	const [quantities, setQuantities] = useState<Record<number, number>>({});
	const [isAdding, setIsAdding] = useState(false);
	const [success, setSuccess] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const { addToCart } = useCart();

	if (!products) {
		return null;
	}
	const handleQuantityChange = (productId: number, value: number) => {
		setQuantities((prev) => ({
			...prev,
			[productId]: Math.max(0, value),
		}));
	};

	const handleAddAllToCart = async () => {
		setError(null);
		setSuccess(false);
		setIsAdding(true);

		try {
			// Get all products with quantities > 0
			const selectedProducts = Object.entries(quantities)
				.filter(([_, qty]) => qty > 0)
				.map(([id, quantity]) => ({
					productId: parseInt(id),
					quantity,
				}));

			if (selectedProducts.length === 0) {
				setError("Please select at least one product");
				setIsAdding(false);
				return;
			}

			const results = [];
			for (const { productId, quantity } of selectedProducts) {
				const result = await addToCart(productId, quantity, [], undefined);
				results.push(result);

				// Small delay between additions to ensure session updates
				await new Promise((resolve) => setTimeout(resolve, 100));
			}

			// Check if any failed
			const failedProducts = results.filter((result) => !result.success);

			if (failedProducts.length > 0) {
				const successCount = results.length - failedProducts.length;
				setError(
					`Added ${successCount} product(s) successfully. Failed to add ${failedProducts.length} product(s). ${
						failedProducts[0]?.error || ""
					}`
				);
			} else {
				setSuccess(true);
				setQuantities({});

				setTimeout(() => setSuccess(false), 3000);
			}
		} catch (err: any) {
			console.error("Error adding products to cart:", err);
			setError(err.message || "An error occurred while adding products to cart");
		} finally {
			setIsAdding(false);
		}
	};

	const hasSelectedProducts = Object.values(quantities).some((qty) => qty > 0);
	const totalItems = Object.values(quantities).reduce((sum, qty) => sum + qty, 0);

	return (
		<div className="grouped-products">
			<h3 className="text-xl font-semibold text-gray-900 mb-4">Choose Products</h3>

			{/* Desktop Table View */}
			<div className="hidden md:block overflow-x-auto">
				<table className="w-full border-collapse">
					<thead>
						<tr className="border-b-2 border-gray-300">
							<th className="text-left py-3 px-4 font-semibold text-gray-700">Product</th>
							<th className="text-left py-3 px-4 font-semibold text-gray-700">Price</th>
							<th className="text-center py-3 px-4 font-semibold text-gray-700 w-32">Quantity</th>
						</tr>
					</thead>
					<tbody>
						{products.map((product) => {
							const quantity = quantities[product.databaseId] || 0;
							const isInStock = product.stockStatus === "IN_STOCK";
							const productPrices = {
								onSale: product.onSale,
								price: product.price,
								regularPrice: product.regularPrice,
								salePrice: product.salePrice,
							};

							return (
								<tr
									key={product.id}
									className={`border-b border-gray-200 transition-colors ${
										quantity > 0 ? "bg-blue-50" : "hover:bg-gray-50"
									}`}
								>
									{/* Product Info */}
									<td className="py-4 px-4">
										<div className="flex items-center gap-4">
											{product.image?.sourceUrl && (
												<div className="relative w-16 h-16 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden">
													<Image
														src={product.image.sourceUrl}
														alt={product.image.altText || product.name}
														fill
														className="object-cover"
														sizes="64px"
													/>
												</div>
											)}
											<div className="flex-1">
												<h4 className="font-medium text-gray-900">{product.name}</h4>
												{!isInStock && (
													<span className="inline-block mt-1 text-xs font-semibold text-red-600 bg-red-50 px-2 py-1 rounded">
														Out of Stock
													</span>
												)}
											</div>
										</div>
									</td>

									{/* Price */}
									<td className="py-4 px-4">
										<ProductPrice prices={productPrices} />
									</td>

									{/* Quantity */}
									<td className="py-4 px-4">
										<div className="flex items-center justify-center gap-2">
											<button
												onClick={() => handleQuantityChange(product.databaseId, quantity - 1)}
												disabled={!isInStock || quantity === 0 || isAdding}
												className="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
												aria-label="Decrease quantity"
											>
												<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
													<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
												</svg>
											</button>
											<input
												type="number"
												min="0"
												value={quantity}
												onChange={(e) => handleQuantityChange(product.databaseId, parseInt(e.target.value) || 0)}
												disabled={!isInStock || isAdding}
												className="w-16 text-center border border-gray-300 rounded-md py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
											/>
											<button
												onClick={() => handleQuantityChange(product.databaseId, quantity + 1)}
												disabled={!isInStock || isAdding}
												className="w-8 h-8 flex items-center justify-center bg-gray-200 hover:bg-gray-300 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed rounded-md transition-colors"
												aria-label="Increase quantity"
											>
												<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
													<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
												</svg>
											</button>
										</div>
									</td>
								</tr>
							);
						})}
					</tbody>
				</table>
			</div>

			{/* Summary and Add to Cart Section */}
			<div className="mt-6 pt-6 border-t border-gray-200">
				{hasSelectedProducts && (
					<div className="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
						<div className="flex items-center justify-between">
							<div>
								<p className="font-medium text-blue-900">
									{Object.values(quantities).filter((q) => q > 0).length} product(s) selected
								</p>
								<p className="text-sm text-blue-700">Total items: {totalItems}</p>
							</div>
							<button
								onClick={() => setQuantities({})}
								disabled={isAdding}
								className="text-sm text-blue-600 hover:text-blue-800 underline disabled:opacity-50"
							>
								Clear all
							</button>
						</div>
					</div>
				)}

				{/* Success Message */}
				{success && (
					<div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
						<svg className="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path
								fillRule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
								clipRule="evenodd"
							/>
						</svg>
						<span className="text-green-800 font-medium">Products added to cart successfully!</span>
					</div>
				)}

				{/* Error Message */}
				{error && (
					<div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
						<svg className="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
							<path
								fillRule="evenodd"
								d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
								clipRule="evenodd"
							/>
						</svg>
						<div className="flex-1">
							<span className="text-red-800 font-medium">{error}</span>
						</div>
						<button
							onClick={() => setError(null)}
							className="text-red-500 hover:text-red-700 ml-2"
							aria-label="Dismiss error"
						>
							<svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
								<path
									fillRule="evenodd"
									d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
									clipRule="evenodd"
								/>
							</svg>
						</button>
					</div>
				)}

				{/* Add to Cart Button */}
				<button
					onClick={handleAddAllToCart}
					disabled={!hasSelectedProducts || isAdding}
					className="w-full md:w-auto bg-blue-600 text-white px-8 py-3 rounded-md hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors font-semibold flex items-center justify-center gap-2"
				>
					{isAdding ? (
						<>
							<svg className="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
								<circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
								<path
									className="opacity-75"
									fill="currentColor"
									d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
								/>
							</svg>
							<span>Adding to Cart...</span>
						</>
					) : (
						<>
							<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									strokeWidth={2}
									d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
								/>
							</svg>
							<span>
								{hasSelectedProducts
									? `Add ${totalItems} Item${totalItems > 1 ? "s" : ""} to Cart`
									: "Select Products to Continue"}
							</span>
						</>
					)}
				</button>
			</div>
		</div>
	);
}
