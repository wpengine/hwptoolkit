import React, { useState } from "react";
import Image from "next/image";
import CartIconSVG from "@/assets/icons/cart-shopping-light-full.svg";
import { useCart } from "@/lib/woocommerce/CartProvider";
import { Product } from "@/interfaces/product.interface";

const CartIcon = ({ className = "w-6 h-6" }) => (
	<span className={`inline-block ${className}`} style={{ filter: "brightness(0) invert(1)" }}>
		<Image src={CartIconSVG} className="cart-icon" alt="Shopping Cart" width={24} height={24} />
	</span>
);

interface AddToCartProps {
	product: Product;
	card?: boolean;
	quantity?: number;
}

export default function AddToCart({ product, card, quantity = 1 }: AddToCartProps) {
	const [isAdding, setIsAdding] = useState(false);
	const [addedToCart, setAddedToCart] = useState(false);

	const { addToCart, refreshCart } = useCart();

	const handleAddToCart = async (e: React.MouseEvent) => {
		e.preventDefault();
		e.stopPropagation();
		if (product.stockStatus === "OUT_OF_STOCK") alert("OUT_OF_STOCK");
		// ✅ Validate quantity
		if (quantity <= 0) {
			alert("Please select a valid quantity");
			return;
		}

		setIsAdding(true);

		try {
			const result = await addToCart(product.databaseId, quantity);

			if (result.errors) {
				console.error("GraphQL errors:", result.errors);
				throw new Error(result.errors[0]?.message || "Failed to add to cart");
			}

			console.log(result.success);

			if (result.success) {
				setAddedToCart(true);
				await refreshCart();
				setTimeout(() => {
					setAddedToCart(false);
				}, 500);
			} else {
				throw new Error("No cart item returned from mutation");
			}
		} catch (error: any) {
			console.error("Add to cart error:", error);
			alert(`Error adding to cart: ${error.message}`);
		} finally {
			setIsAdding(false);
		}
	};

	const getButtonText = () => {
		const icon = <CartIcon className="w-4 h-4 mr-1" />;
		let html = <>{icon} Add to Cart</>;
		if (product.stockStatus === "OUT_OF_STOCK") return (
			<>
				<span className="inline-flex items-center justify-center w-4 h-4 mr-1 bg-red-600 rounded-full">
					<svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
					</svg>
				</span>
				Out of Stock
			</>
		);
		if (isAdding) return "Adding...";
		if (addedToCart) return "Added to Cart!";
		return html;
	};

	const getButtonClass = () => {
		if (product.stockStatus === "OUT_OF_STOCK") return "text-red-400 font-semibold";
		let baseClass =
			"add-to-cart-btn flex items-center justify-center cursor-pointer py-2 px-6 rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-600 text-white hover:bg-blue-700";
		if (!card) baseClass += " w-full";
		if (card) baseClass += " text-sm";
		if (addedToCart) baseClass += " added";
		if (isAdding) baseClass += " loading";
		return baseClass;
	};

	return (
		<div className="flex items-center justify-center w-full">
			{product.externalUrl ? (
				<a href={product.externalUrl} target="_blank" rel="noopener noreferrer" className={getButtonClass()}>
					External Link
				</a>
			) : product.type === "VARIABLE" ? (
				<button className={getButtonClass()} disabled={true}>
					Select Options
				</button>
			) : (
				<button
					className={getButtonClass()}
					disabled={product.stockStatus === "OUT_OF_STOCK" || isAdding}
					onClick={handleAddToCart}
				>
					{getButtonText()}
				</button>
			)}
			<style jsx>{`
				.cart-icon {
					fill: white;
				}
			`}</style>
		</div>
	);
}
