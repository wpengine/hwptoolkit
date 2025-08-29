import React, { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { Product } from "@/interfaces/product.interface";
import { useCart } from "@/lib/woocommerce/cartContext";
import useCartMutations from "@/lib/woocommerce/useCartMutations";

interface SingleProductProps {
	product: Product;
}

export default function SingleProduct({ product }: SingleProductProps) {
	const [isAdding, setIsAdding] = useState(false);
	const [addedToCart, setAddedToCart] = useState(false);
	const [quantity, setQuantity] = useState<number>(1);
	const { quantityInCart: inCart, mutate, loading } = useCartMutations(product.databaseId);
	useEffect(() => {
		if (inCart) {
			setQuantity(inCart);
		}
	}, [inCart]);

	if (loading) return <p>Loading...</p>;

	const handleAddOrUpdateAction = async () => {
		setIsAdding(true);
		mutate({ quantity });
		setIsAdding(false);
		setAddedToCart(true);
	};

	const handleRemoveAction = async () => {
		mutate({ mutation: "remove", quantity: 0 });
	};

	const buttonText = inCart ? "Update" : "Add To Cart";

	// const { addToCart, getCartItemCount, loading } = useCart();

	const [activeTab, setActiveTab] = useState<string>("description");

	if (!product) {
		return null;
	}

	const getStockStatusColor = (status: string) => {
		switch (status) {
			case "IN_STOCK":
				return "text-green-600 bg-green-100";
			case "OUT_OF_STOCK":
				return "text-red-600 bg-red-100";
			case "ON_BACKORDER":
				return "text-yellow-600 bg-yellow-100";
			default:
				return "text-gray-600 bg-gray-100";
		}
	};

	const getStockStatusText = (status: string) => {
		switch (status) {
			case "IN_STOCK":
				return "In Stock";
			case "OUT_OF_STOCK":
				return "Out of Stock";
			case "ON_BACKORDER":
				return "On Backorder";
			default:
				return status;
		}
	};

	const renderStars = (rating: number) => {
		const stars = [];
		const fullStars = Math.floor(rating);
		const hasHalfStar = rating % 1 !== 0;

		for (let i = 0; i < 5; i++) {
			if (i < fullStars) {
				stars.push(
					<svg key={i} className="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
						<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
					</svg>
				);
			} else if (i === fullStars && hasHalfStar) {
				stars.push(
					<svg key={i} className="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
						<defs>
							<linearGradient id="half-star">
								<stop offset="50%" stopColor="currentColor" />
								<stop offset="50%" stopColor="#e5e7eb" />
							</linearGradient>
						</defs>
						<path
							fill="url(#half-star)"
							d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
						/>
					</svg>
				);
			} else {
				stars.push(
					<svg key={i} className="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20">
						<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
					</svg>
				);
			}
		}
		return stars;
	};

	const galleryImages = product.galleryImages?.nodes || [];
	const allImages = product.image ? [product.image, ...galleryImages] : galleryImages;

	const handleAddToCart = async (e: React.MouseEvent) => {
		e.preventDefault();
		e.stopPropagation();

		if (quantity <= 0) {
			alert("Please select a valid quantity");
			return;
		}

		setIsAdding(true);

		try {
			console.log(`Adding ${quantity} of product ${product.name} (ID: ${product.databaseId}) to cart`);

			const result = await addToCart(product.databaseId, quantity);

			if (result.success) {
				setAddedToCart(true);
				console.log("Product added to cart successfully:", result.cartItem);

				// Reset the added state after 2 seconds
				setTimeout(() => {
					setAddedToCart(false);
				}, 2000);
				console.log(getCartItemCount());
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
			{/* Breadcrumb */}
			<nav className="flex mb-8 text-sm">
				<Link href="/" className="text-blue-600 hover:text-blue-800">
					Home
				</Link>
				<span className="mx-2 text-gray-500">/</span>
				<Link href="/products" className="text-blue-600 hover:text-blue-800">
					Products
				</Link>
				<span className="mx-2 text-gray-500">/</span>
				<span className="text-gray-700">{product.name}</span>
			</nav>

			<div className="grid lg:grid-cols-2 gap-12 mb-12">
				{/* Product Images */}
				<div className="space-y-4">
					{/* Main Image */}
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

						{/* Sale Badge */}
						{product.onSale && (
							<div className="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium">
								Sale
							</div>
						)}
					</div>

					{/* Thumbnail Gallery */}
					{allImages.length > 1 && (
						<div className="grid grid-cols-4 gap-2">
							{allImages.map((image, index) => (
								<button
									key={image.id}
									className="relative aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 border-transparent hover:border-gray-300 transition-colors"
								>
									<Image
										src={image.sourceUrl}
										alt={image.altText || `${product.name} image ${index + 1}`}
										fill
										className="object-cover"
									/>
								</button>
							))}
						</div>
					)}
				</div>

				{/* Product Info */}
				<div className="space-y-6">
					<div>
						<h1 className="text-3xl font-bold text-gray-900 mb-2">{product.name}</h1>
						{product.sku && <p className="text-gray-600">SKU: {product.sku}</p>}
					</div>

					{/* Rating */}
					<div className="flex items-center space-x-2">
						<div className="flex">{renderStars(product.averageRating)}</div>
						<span className="text-gray-600">({product.reviewCount} reviews)</span>
					</div>

					{/* Price */}
					<div className="space-y-2">
						{product.onSale ? (
							<div className="flex items-center space-x-3">
								<span className="text-3xl font-bold text-red-600">{product.salePrice}</span>
								<span className="text-xl text-gray-500 line-through">{product.regularPrice}</span>
							</div>
						) : (
							<span className="text-3xl font-bold text-gray-900">{product.price || ""}</span>
						)}
					</div>

					{/* Stock Status */}
					<div>
						<span
							className={`inline-block px-3 py-1 rounded-full text-sm font-medium ${getStockStatusColor(
								product.stockStatus
							)}`}
						>
							{getStockStatusText(product.stockStatus)}
						</span>
						{product.stockQuantity && <span className="ml-2 text-gray-600">{product.stockQuantity} in stock</span>}
					</div>

					{/* Short Description */}
					{product.shortDescription && (
						<div
							className="text-gray-700 leading-relaxed"
							dangerouslySetInnerHTML={{ __html: product.shortDescription }}
						/>
					)}

					{/* Categories */}
					{product.productCategories.nodes.length > 0 && (
						<div>
							<span className="text-sm font-medium text-gray-700">Categories: </span>
							<div className="inline-flex flex-wrap gap-2 mt-1">
								{product.productCategories.nodes.map((category) => (
									<Link
										key={category.id}
										href={`/product-category/${category.slug}`}
										className="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-sm hover:bg-blue-200 transition-colors"
									>
										{category.name}
									</Link>
								))}
							</div>
						</div>
					)}

					{/* Tags */}
					{product.productTags.nodes.length > 0 && (
						<div>
							<span className="text-sm font-medium text-gray-700">Tags: </span>
							<div className="inline-flex flex-wrap gap-2 mt-1">
								{product.productTags.nodes.map((tag) => (
									<Link
										key={tag.id}
										href={`/product-tag/${tag.slug}`}
										className="px-2 py-1 bg-gray-100 text-gray-800 rounded-md text-sm hover:bg-gray-200 transition-colors"
									>
										#{tag.name}
									</Link>
								))}
							</div>
						</div>
					)}

					{/* Add to Cart */}
					{product.stockStatus === "IN_STOCK" && (
						<div className="space-y-4">
							<div className="flex items-center space-x-4">
								<label htmlFor="quantity" className="text-sm font-medium text-gray-700">
									Quantity:
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

							<button
								disabled={isAdding || loading}
								onClick={handleAddOrUpdateAction}
								className={`w-full py-3 px-6 rounded-md font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 ${
									isAdding || loading
										? "bg-gray-400 text-gray-600 cursor-not-allowed"
										: addedToCart
										? "bg-green-600 text-white"
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
										Adding to Cart...
									</span>
								) : addedToCart ? (
									<span className="flex items-center justify-center">
										<svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
										</svg>
										Added to Cart!
									</span>
								) : (
									`Add ${quantity > 1 ? `${quantity} ` : ""}to Cart`
								)}
							</button>
						</div>
					)}
				</div>
			</div>

			{/* Product Details Tabs */}
			<div className="border-t border-gray-200 pt-8">
				<div className="flex space-x-8 mb-8">
					{[
						{ id: "description", label: "Description" },
						{ id: "additional", label: "Additional Information" },
						{ id: "reviews", label: `Reviews (${product.reviewCount})` },
					].map((tab) => (
						<button
							key={tab.id}
							onClick={() => setActiveTab(tab.id)}
							className={`pb-2 border-b-2 font-medium transition-colors ${
								activeTab === tab.id
									? "border-blue-500 text-blue-600"
									: "border-transparent text-gray-500 hover:text-gray-700"
							}`}
						>
							{tab.label}
						</button>
					))}
				</div>

				<div className="min-h-[200px]">
					{activeTab === "description" && (
						<div className="prose max-w-none">
							{product.description ? (
								<div dangerouslySetInnerHTML={{ __html: product.description }} />
							) : (
								<p className="text-gray-600">No description available.</p>
							)}
						</div>
					)}

					{activeTab === "additional" && (
						<div className="space-y-4">
							<h3 className="text-lg font-semibold">Additional Information</h3>
							<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
								{product.weight && (
									<div className="flex justify-between py-2 border-b border-gray-200">
										<span className="font-medium">Weight:</span>
										<span>{product.weight}</span>
									</div>
								)}
								{product.length && (
									<div className="flex justify-between py-2 border-b border-gray-200">
										<span className="font-medium">Length:</span>
										<span>{product.length}</span>
									</div>
								)}
								{product.width && (
									<div className="flex justify-between py-2 border-b border-gray-200">
										<span className="font-medium">Width:</span>
										<span>{product.width}</span>
									</div>
								)}
								{product.height && (
									<div className="flex justify-between py-2 border-b border-gray-200">
										<span className="font-medium">Height:</span>
										<span>{product.height}</span>
									</div>
								)}
								{product.attributes.nodes.map((attribute) => (
									<div key={attribute.id} className="flex justify-between py-2 border-b border-gray-200">
										<span className="font-medium">{attribute.name}:</span>
										<span>{attribute.options?.join(", ") || "N/A"}</span>
									</div>
								))}
							</div>
						</div>
					)}

					{activeTab === "reviews" && (
						<div className="space-y-4">
							<h3 className="text-lg font-semibold">Customer Reviews</h3>
							<div className="bg-gray-50 p-6 rounded-lg text-center">
								<p className="text-gray-600">Reviews component would go here.</p>
								<p className="text-sm text-gray-500 mt-2">Integration with WooCommerce reviews system needed.</p>
							</div>
						</div>
					)}
				</div>
			</div>
		</div>
	);
}
