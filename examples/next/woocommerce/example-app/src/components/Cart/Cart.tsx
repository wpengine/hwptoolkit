import React, { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { useCartMutations, useOtherCartMutations } from "@/lib/woocommerce/cart";
import { useCart } from "@/lib/woocommerce/CartProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";

export default function Cart() {
	const { cart, cartItems, loading, refreshCart, isInitialized } = useCart();

	const { updateItemQuantities, removeItemsFromCart, loading: mutationLoading } = useCartMutations();
	const { applyCoupon, removeCoupons, loading: couponLoading } = useOtherCartMutations();

	const [couponCode, setCouponCode] = useState("");
	const [updatingItems, setUpdatingItems] = useState({});

	if (!isInitialized) {
		return <LoadingSpinner />;
	}
	
	const handleQuantityUpdate = async (cartKey: string, newQuantity: number) => {
		if (newQuantity < 1) {
			handleRemoveItem(cartKey);
			return;
		}

		setUpdatingItems((prev) => ({ ...prev, [cartKey]: true }));

		try {
			const { data } = await updateItemQuantities({
				variables: {
					input: {
						items: [
							{
								key: cartKey,
								quantity: newQuantity,
							},
						],
					},
				},
			});

			if (data?.updateItemQuantities) {
				await refreshCart();
			}
		} catch (error) {
			console.error("Error updating quantity:", error);
		} finally {
			setUpdatingItems((prev) => ({ ...prev, [cartKey]: false }));
		}
	};

	const handleRemoveItem = async (cartKey: string) => {
		setUpdatingItems((prev) => ({ ...prev, [cartKey]: true }));

		try {
			const { data } = await removeItemsFromCart({
				variables: {
					input: {
						keys: [cartKey],
					},
				},
			});

			if (data?.removeItemsFromCart) {
				await refreshCart();
			}
		} catch (error) {
			console.error("Error removing item:", error);
		} finally {
			setUpdatingItems((prev) => ({ ...prev, [cartKey]: false }));
		}
	};

	const handleApplyCoupon = async (e: React.FormEvent) => {
		e.preventDefault();
		if (!couponCode.trim()) return;

		try {
			const { data } = await applyCoupon({
				variables: {
					input: {
						code: couponCode,
					},
				},
			});

			if (data?.applyCoupon) {
				setCouponCode("");
				await refreshCart();
			}
		} catch (error) {
			console.error("Error applying coupon:", error);
		}
	};

	const handleRemoveCoupon = async (couponCode: string) => {
		try {
			const { data } = await removeCoupons({
				variables: {
					input: {
						codes: [couponCode],
					},
				},
			});

			if (data?.removeCoupons) {
				await refreshCart();
			}
		} catch (error) {
			console.error("Error removing coupon:", error);
		}
	};

	if (loading) {
		return (
			<div className="flex items-center justify-center min-h-96">
				<div className="text-lg text-gray-600">Loading cart...</div>
			</div>
		);
	}

	if (!cart || cart.isEmpty) {
		return (
			<div className="text-center py-16">
				<div className="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
					<svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth={1.5}
							d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"
						/>
					</svg>
				</div>
				<h2 className="text-2xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
				<p className="text-gray-600 mb-6">Add some products to get started!</p>
				<Link
					href="/shop"
					className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium"
				>
					Continue Shopping
				</Link>
			</div>
		);
	}

	return (
		<div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
			<h1 className="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

			{/* 2-Column Layout */}
			<div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
				{/* Left Column - Cart Items */}
				<div className="lg:col-span-2">
					<div className="bg-white rounded-lg shadow-sm border border-gray-200">
						<div className="p-6">
							<div className="space-y-6">
								{cartItems.map((item) => {
									const product = item.product.node;
									const variation = item.variation?.node;
									const isUpdating = updatingItems[item.key];

									return (
										<div
											key={item.key}
											className={`flex items-start space-x-4 pb-6 border-b border-gray-200 last:border-b-0 last:pb-0 ${
												isUpdating ? "opacity-60" : ""
											}`}
										>
											{/* Product Image */}
											<div className="flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24">
												<Image
													src={
														variation?.image?.featuredImage?.sourceUrl ||
														product.featuredImage.node.sourceUrl ||
														"/placeholder.jpg"
													}
													alt={variation?.image?.altText || product.image?.altText || product.name}
													width={96}
													height={96}
													className="w-full h-full object-cover rounded-lg"
												/>
											</div>

											{/* Product Info */}
											<div className="flex-1 min-w-0">
												<Link
													href={`/product/${product.slug}`}
													className="text-lg font-medium text-gray-900 hover:text-blue-600 transition-colors"
												>
													{product.name}
												</Link>
												<div>
													{variation && <p className="text-sm text-gray-500 mt-1">{variation.name}</p>}
													<p className="text">Subtotal:{item.subtotal}</p>
													<p className="text-xs">Tax: {item.subtotalTax}</p>
												</div>
												{/* Mobile Quantity and Remove */}
												<div className="flex items-center justify-between mt-4 sm:hidden">
													<div className="flex items-center border border-gray-300 rounded-md">
														<button
															onClick={() => handleQuantityUpdate(item.key, item.quantity - 1)}
															disabled={isUpdating || item.quantity <= 1}
															className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
														>
															<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
																<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
															</svg>
														</button>
														<span className="px-3 py-2 text-sm font-medium text-gray-900 min-w-[50px] text-center">
															{item.quantity}
														</span>
														<button
															onClick={() => handleQuantityUpdate(item.key, item.quantity + 1)}
															disabled={isUpdating}
															className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
														>
															<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
																<path
																	strokeLinecap="round"
																	strokeLinejoin="round"
																	strokeWidth={2}
																	d="M12 6v6m0 0v6m0-6h6m-6 0H6"
																/>
															</svg>
														</button>
													</div>
													<div className="flex flex-col items-end">
														<span className="text-lg font-bold text-gray-900 mb-2">{item.total}</span>
														<button
															onClick={() => handleRemoveItem(item.key)}
															disabled={isUpdating}
															className="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50"
														>
															{isUpdating ? "Removing..." : "Remove"}
														</button>
													</div>
												</div>
											</div>

											{/* Desktop Quantity Controls */}
											<div className="hidden sm:flex items-center space-x-8">
												<div className="flex items-center border border-gray-300 rounded-md">
													<button
														onClick={() => handleQuantityUpdate(item.key, item.quantity - 1)}
														disabled={isUpdating || item.quantity <= 1}
														className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
													>
														<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
															<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
														</svg>
													</button>
													<span className="px-4 py-2 text-sm font-medium text-gray-900 min-w-[60px] text-center">
														{item.quantity}
													</span>
													<button
														onClick={() => handleQuantityUpdate(item.key, item.quantity + 1)}
														disabled={isUpdating}
														className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
													>
														<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
															<path
																strokeLinecap="round"
																strokeLinejoin="round"
																strokeWidth={2}
																d="M12 6v6m0 0v6m0-6h6m-6 0H6"
															/>
														</svg>
													</button>
												</div>

												{/* Desktop Total and Remove */}
												<div className="flex flex-col items-end space-y-2 min-w-[120px]">
													<span className="text-lg font-bold text-gray-900">{item.total}</span>
													<button
														onClick={() => handleRemoveItem(item.key)}
														disabled={isUpdating}
														className="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50"
													>
														{isUpdating ? "Removing..." : "Remove"}
													</button>
												</div>
											</div>
										</div>
									);
								})}
							</div>
						</div>
					</div>
				</div>

				{/* Right Column - Order Summary */}
				<div className="lg:col-span-1">
					<div className="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-8">
						<div className="p-6">
							<h3 className="text-xl font-semibold text-gray-900 mb-6">Order Summary</h3>

							{/* Summary Rows */}
							<div className="space-y-3">
								<div className="flex justify-between text-sm">
									<span className="text-gray-600">Subtotal:</span>
									<span className="font-medium text-gray-900">{cart.subtotal}</span>
								</div>

								{cart.discountTotal && cart.discountTotal !== "0" && (
									<div className="flex justify-between text-sm">
										<span className="text-gray-600">Discount:</span>
										<span className="font-medium text-red-600">-{cart.discountTotal}</span>
									</div>
								)}

								{cart.shippingTotal && cart.shippingTotal !== "0" && (
									<div className="flex justify-between text-sm">
										<span className="text-gray-600">Shipping:</span>
										<span className="font-medium text-gray-900">{cart.shippingTotal}</span>
									</div>
								)}

								{cart.totalTax && cart.totalTax !== "0" && (
									<div className="flex justify-between text-sm">
										<span className="text-gray-600">Tax:</span>
										<span className="font-medium text-gray-900">{cart.totalTax}</span>
									</div>
								)}

								<div className="border-t border-gray-200 pt-3">
									<div className="flex justify-between">
										<span className="text-lg font-semibold text-gray-900">Total:</span>
										<span className="text-lg font-bold text-gray-900">{cart.total}</span>
									</div>
								</div>
							</div>

							{/* Applied Coupons */}
							{cart.appliedCoupons && cart.appliedCoupons.length > 0 && (
								<div className="mt-6 p-4 bg-green-50 rounded-md">
									<h4 className="text-sm font-semibold text-green-800 mb-2">Applied Coupons</h4>
									<div className="space-y-2">
										{cart.appliedCoupons.map((coupon) => (
											<div key={coupon.code} className="flex justify-between items-center text-sm">
												<span className="text-green-700 font-medium">{coupon.code}</span>
												<div className="flex items-center space-x-2">
													<span className="text-green-700">-{coupon.discountAmount}</span>
													<button
														onClick={() => handleRemoveCoupon(coupon.code)}
														disabled={couponLoading}
														className="text-red-500 hover:text-red-700 font-bold text-xs w-5 h-5 rounded-full border border-red-300 hover:border-red-500 disabled:opacity-50"
													>
														×
													</button>
												</div>
											</div>
										))}
									</div>
								</div>
							)}

							{/* Coupon Form */}
							<form onSubmit={handleApplyCoupon} className="mt-6">
								<div className="flex space-x-2">
									<input
										type="text"
										placeholder="Coupon code"
										value={couponCode}
										onChange={(e) => setCouponCode(e.target.value)}
										className="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
									/>
									<button
										type="submit"
										disabled={couponLoading || !couponCode.trim()}
										className="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
									>
										{couponLoading ? "Applying..." : "Apply"}
									</button>
								</div>
							</form>

							{/* Checkout Button */}
							<Link
								href="/checkout"
								className="block w-full mt-6 bg-green-600 text-white text-center py-3 px-4 rounded-md hover:bg-green-700 transition-colors font-semibold text-lg"
							>
								Proceed to Checkout
							</Link>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
}
