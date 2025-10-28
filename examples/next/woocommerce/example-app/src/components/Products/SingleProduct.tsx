import React, { useState } from "react";
import Image from "next/image";
import { Product } from "@/interfaces/product.interface";
import { useCart } from "@/lib/woocommerce/CartProvider";
interface SingleProductProps {
	product: Product;
}

export default function SingleProduct({ product }: SingleProductProps) {
	const [isAdding, setIsAdding] = useState(false);
	const [addedToCart, setAddedToCart] = useState(false);
	const [quantity, setQuantity] = useState<number>(1);
	const [activeTab, setActiveTab] = useState<string>("description");

	const { addToCart, findCartItem, loading, cart } = useCart();

	if (!product) {
		return null;
	}

	// Check if product is already in cart
	const existingCartItem = findCartItem(product.databaseId);
	const isInCart = !!existingCartItem;
	const currentCartQuantity = existingCartItem?.quantity || 0;
	// Add this to your app for debugging

	const handleAddToCart = async (e: React.MouseEvent) => {
		e.preventDefault();
		e.stopPropagation();

		if (quantity <= 0) {
			alert("Please select a valid quantity");
			return;
		}

		setIsAdding(true);

		try {
			console.log(
				`${isInCart ? "Updating" : "Adding"} ${quantity} of product ${product.name} (ID: ${product.databaseId}) to cart`
			);

			const result = await addToCart(product.databaseId, quantity);

			console.log("Cart operation result:", result);

			if (result.success) {
				setAddedToCart(true);
				console.log(`Product ${result.action} successfully!`);

				// Reset the added state after 2 seconds
				setTimeout(() => {
					setAddedToCart(false);
				}, 2000);
			} else {
				throw new Error(result.error || "Failed to add to cart");
			}
		} catch (error) {
			console.error("Add to cart error:", error);
			alert(`Error adding to cart: ${error.message || error}`);
		} finally {
			setIsAdding(false);
		}
	};

	return (
		<div className="container mx-auto px-4 py-8">
			{/* Debug info */}

			<div className="mb-4 p-4 bg-gray-100 rounded-lg text-sm">
				<p>
					<strong>Debug Info:</strong>
				</p>
				<p>Product ID: {product.databaseId}</p>
				<p>Is in cart: {isInCart.toString()}</p>
				<p>Current cart quantity: {currentCartQuantity}</p>
				<p>
					Will {isInCart ? "update to" : "add"}: {isInCart ? currentCartQuantity + quantity : quantity}
				</p>
			</div>

			{/* Your existing JSX... */}
			<div className="grid lg:grid-cols-2 gap-12 mb-12">
				{/* Product Images */}
				<div className="space-y-4">
					<div className="relative aspect-square bg-gray-100 rounded-lg overflow-hidden">
						{product.image ? (
							<Image
								src={product.image.sourceUrl}
								alt={product.image.altText || product.name}
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
						<h1 className="text-3xl font-bold text-gray-900 mb-2">{product.name}</h1>
						{product.sku && <p className="text-gray-600">SKU: {product.sku}</p>}
					</div>

					{/* Show current cart status */}
					{isInCart && (
						<div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
							<p className="text-blue-800 text-sm">
								📦 Currently in cart: <strong>{currentCartQuantity}</strong>{" "}
								{currentCartQuantity === 1 ? "item" : "items"}
							</p>
						</div>
					)}

					{/* Add to Cart */}
					{product.stockStatus === "IN_STOCK" && (
						<div className="space-y-4">
							<div className="flex items-center space-x-4">
								<label htmlFor="quantity" className="text-sm font-medium text-gray-700">
									Quantity to {isInCart ? "add" : "add"}:
								</label>
								<select
									id="quantity"
									value={quantity}
									onChange={(e) => setQuantity(parseInt(e.target.value))}
									className="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
								>
									{[...Array(Math.min(10, product.stockQuantity || 10))].map((_, i) => (
										<option key={i + 1} value={i + 1}>
											{i + 1}
										</option>
									))}
								</select>
							</div>

							{isInCart && (
								<p className="text-sm text-gray-600">
									New total will be: <strong>{currentCartQuantity + quantity}</strong>
								</p>
							)}

							<button
								disabled={isAdding || loading}
								onClick={handleAddToCart}
								className={`w-full py-3 px-6 rounded-md font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ${
									isAdding || loading
										? "bg-gray-400 text-gray-600 cursor-not-allowed"
										: addedToCart
										? "bg-green-600 text-white"
										: isInCart
										? "bg-orange-600 text-white hover:bg-orange-700"
										: "bg-blue-600 text-white hover:bg-blue-700"
								}`}
							>
								{isAdding || loading ? (
									<span className="flex items-center justify-center">
										<svg
											className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
											xmlns="http://www.w3.org/2000/svg"
											fill="none"
											viewBox="0 0 24 24"
										>
											<circle
												className="opacity-25"
												cx="12"
												cy="12"
												r="10"
												stroke="currentColor"
												strokeWidth="4"
											></circle>
											<path
												className="opacity-75"
												fill="currentColor"
												d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
											></path>
										</svg>
										{isInCart ? "Updating Cart..." : "Adding to Cart..."}
									</span>
								) : addedToCart ? (
									<span className="flex items-center justify-center">
										<svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
										</svg>
										{isInCart ? "Updated Cart!" : "Added to Cart!"}
									</span>
								) : isInCart ? (
									`Add ${quantity} More (Total: ${currentCartQuantity + quantity})`
								) : (
									`Add ${quantity > 1 ? `${quantity} ` : ""}to Cart`
								)}
							</button>
						</div>
					)}
				</div>
			</div>
		</div>
	);
}
