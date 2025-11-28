import React from "react";
import { Product } from "@/interfaces/product.interface";

interface ProductQuantityProps {
	product: Product;
	quantity: number;
	setQuantity: (quantity: number) => void;
}

export default function ProductQuantity({ product, quantity, setQuantity }: ProductQuantityProps) {
	if (product.stockStatus === "OUT_OF_STOCK") {
		return null;
	}

	return (
		<input
			type="number"
			id="quantity"
			value={quantity}
			onChange={(e) => setQuantity(parseInt(e.target.value) || 1)}
			min="1"
			max={Math.min(10, product.stockQuantity || 10)}
			className="w-20 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
		/>
	);
}
