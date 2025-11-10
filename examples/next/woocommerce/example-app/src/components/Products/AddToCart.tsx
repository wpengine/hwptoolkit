import React, { useState } from "react";
import { useCart } from "@/lib/providers/CartProvider";
import { Product } from "@/interfaces/product.interface";
import Link from "next/dist/client/link";
import { AddToCartProps } from "@/interfaces/product.interface";

export default function AddToCart({ product, card = null, quantity = 1, variation = null, variationId = null }: AddToCartProps) {
	const { addToCart, cartLoading } = useCart();
	const [isAdding, setIsAdding] = useState(false);
	const [error, setError] = useState<string | null>(null);
	const [success, setSuccess] = useState(false);

	const handleAddToCart = async () => {
		setIsAdding(true);
		setError(null);
		setSuccess(false);

		try {
			console.log("Adding to cart with:", {
				productId: product.databaseId,
				quantity,
				variation,
				variationId,
			});

			const result = await addToCart(
				product.databaseId,
				quantity,
				variation, // ✅ Pass the array directly - WooCommerce will handle it
				variationId
			);

			if (result.success) {
				setSuccess(true);
				
				setTimeout(() => setSuccess(false), 3000);
			} else {
				setError(result.error || "Failed to add to cart");
			}
		} catch (err: any) {
			console.error("Error adding to cart:", err);
			setError(err.message || "An error occurred");
		} finally {
			setIsAdding(false);
		}
	};

	// ✅ Remove variation selection requirement - allow adding to cart anytime
	const hasVariations = product.variations?.nodes && product.variations.nodes.length > 0;
	const isDisabled = isAdding || cartLoading;

	return (
		<div className="add-to-cart w-full">
			{hasVariations && (!variation || variation.length === 0) && card ? (
				<Link
					href={`/product/${product.slug}`}
					className="px-6 py-3 rounded-lg font-semibold transition-all bg-blue-600 text-white hover:bg-blue-700"
				>
					Select Variation
				</Link>
			) : (
				<button
					onClick={handleAddToCart}
					disabled={isDisabled}
					className={`px-6 py-3 rounded-lg font-semibold transition-all ${
						success
							? "bg-green-600 text-white"
							: isDisabled
							? "bg-gray-300 text-gray-500 cursor-not-allowed"
							: "bg-blue-600 text-white hover:bg-blue-700 active:scale-95"
					}
                    ${card ? "" : "w-full"}
                    `}
				>
					{isAdding ? (
						<span className="flex items-center justify-center">
							<svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
								<circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
								<path
									className="opacity-75"
									fill="currentColor"
									d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
								/>
							</svg>
							Adding...
						</span>
					) : success ? (
						<span className="flex items-center justify-center">
							<svg className="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
								<path
									fillRule="evenodd"
									d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
									clipRule="evenodd"
								/>
							</svg>
							Added to Cart!
						</span>
					) : (
						"Add to Cart"
					)}
				</button>
			)}

			{/* ✅ Error message */}
			{error && (
				<div className="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
					<p className="text-sm text-red-800">{error}</p>
				</div>
			)}
		</div>
	);
}
