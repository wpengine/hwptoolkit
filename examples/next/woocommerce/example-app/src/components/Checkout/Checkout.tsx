import React, { useState, useEffect } from "react";
import Image from "next/image";
import Link from "next/link";
import { useLazyQuery, useMutation } from "@apollo/client";

import { useCart } from "@/lib/providers/CartProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import { GET_CART } from "@/lib/graphQL/cartGraphQL";
import type { Cart as CartType, GetCartResponse, CartItem } from "@/interfaces/cart.interface";
import Addresses from "@/components/Account/Tabs/Addresses";
import { GET_USER_SETTINGS } from "@/lib/graphQL/userGraphQL";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
import { Customer } from "@/interfaces/customer.interface";
import { CHECKOUT_MUTATION } from "@/lib/graphQL/cartGraphQL";
// ✅ Add proper type for GET_USER_SETTINGS response
interface GetCustomerResponse {
	customer: Customer;
}

export default function Checkout() {
	const {
		cart: providerCart,
		updateCartItemQuantity,
		removeItem,
		clearCart,
		clearingCart,
		cartLoading: providerLoading,
		refreshCart,
		applyCoupon,
		removeCoupons,
	} = useCart();

	const [fullCart, setFullCart] = useState<CartType | null>(null);
	const [couponCode, setCouponCode] = useState<string>("");
	const [updatingItems, setUpdatingItems] = useState<Record<string, boolean>>({});
	const [couponError, setCouponError] = useState<string | null>(null);
	const [couponSuccess, setCouponSuccess] = useState<string | null>(null);
	const [applyingCoupon, setApplyingCoupon] = useState(false);
	const [customer, setCustomer] = useState<Customer | null>(null);
	const [checkoutProcessing, setCheckoutProcessing] = useState(false);
	const { user, isLoading: authLoading } = useAuthAdmin();
	const isAuthenticated = !!user;

	const [getCustomer, { loading: userDataLoading }] = useLazyQuery<GetCustomerResponse>(GET_USER_SETTINGS, {
		onCompleted: (data) => {
			if (data?.customer) {
				setCustomer(data.customer);
			}
		},
		onError: (error) => console.error("GetCustomer error:", error),
		fetchPolicy: "network-only",
		errorPolicy: "all",
	});

	const [getFullCartQuery, { loading: fullCartLoading }] = useLazyQuery<GetCartResponse>(GET_CART, {
		onCompleted: (data) => {
			if (data?.cart) {
				setFullCart(data.cart);
			}
		},
		onError: (error) => console.error("GetCart error:", error),
		fetchPolicy: "network-only",
		errorPolicy: "all",
	});

	// ✅ Fetch customer data only when authenticated
	useEffect(() => {
		if (isAuthenticated) {
			console.log("Fetching customer data...");
			getCustomer();
		} else {
			setCustomer(null);
		}
	}, [isAuthenticated, getCustomer]);

	// Fetch full cart on mount
	useEffect(() => {
		getFullCartQuery();
	}, [getFullCartQuery]);

	// Refresh full cart when provider cart changes
	useEffect(() => {
		if (providerCart) {
			getFullCartQuery();
		} else {
			setFullCart(null);
		}
	}, [providerCart, getFullCartQuery]);

	const cart = fullCart || (providerCart as CartType);
	const isLoading = fullCartLoading || providerLoading || userDataLoading;

	const handleRemoveItem = async (cartKey: string) => {
		setUpdatingItems((prev) => ({ ...prev, [cartKey]: true }));

		try {
			await removeItem(cartKey);
			await getFullCartQuery();
		} catch (error) {
			console.error("Error removing item:", error);
		} finally {
			setUpdatingItems((prev) => ({ ...prev, [cartKey]: false }));
		}
	};

	const handleUpdateQuantity = async (itemKey: string, newQuantity: number) => {
		setUpdatingItems((prev) => ({ ...prev, [itemKey]: true }));

		try {
			await updateCartItemQuantity(itemKey, newQuantity);
		} catch (error) {
			console.error("Error updating quantity:", error);
		} finally {
			setUpdatingItems((prev) => ({ ...prev, [itemKey]: false }));
		}
	};

	const handleApplyCoupon = async (e: React.FormEvent) => {
		e.preventDefault();
		if (!couponCode.trim()) return;

		setCouponError(null);
		setCouponSuccess(null);
		setApplyingCoupon(true);

		try {
			const result = await applyCoupon(couponCode);

			if (result.success) {
				setCouponSuccess(`Coupon "${couponCode}" applied successfully!`);
				setCouponCode("");
				await refreshCart();
				await getFullCartQuery();

				setTimeout(() => setCouponSuccess(null), 3000);
			} else {
				const error = result.error.replace(/&quot;/g, '"');
				setCouponError(error || "Failed to apply coupon");
				setTimeout(() => setCouponError(null), 3000);
			}
		} catch (error: any) {
			setCouponError(error.message || "An error occurred while applying the coupon");
			setTimeout(() => setCouponError(null), 3000);
		} finally {
			setApplyingCoupon(false);
		}
	};

	const handleRemoveCoupon = async (code: string) => {
		setCouponError(null);
		setCouponSuccess(null);

		try {
			const result = await removeCoupons([code]);

			if (result.success) {
				setCouponSuccess(`Coupon "${code}" removed successfully!`);
				await refreshCart();
				await getFullCartQuery();

				setTimeout(() => setCouponSuccess(null), 3000);
			} else {
				setCouponError(result.error || "Failed to remove coupon");
			}
		} catch (error: any) {
			console.error("Error removing coupon:", error);
			setCouponError(error.message || "An error occurred while removing the coupon");
		}
	};
	const [checkoutMutation, { loading: checkoutLoading }] = useMutation(CHECKOUT_MUTATION, {
		onCompleted: (data) => {
			console.log("Checkout completed:", data);
		},
		onError: (error) => console.error("❌ Update cart error:", error),
	});
		const removeTypename = (obj: any): any => {
		if (!obj) return obj;

		if (Array.isArray(obj)) {
			return obj.map(removeTypename);
		}

		if (typeof obj === "object") {
			const newObj: any = {};
			Object.keys(obj).forEach((key) => {
				if (key !== "__typename") {
					newObj[key] = removeTypename(obj[key]);
				}
			});
			return newObj;
		}

		return obj;
	};
	const handleCheckout = async () => {
		// Implement checkout logic here
		console.log("Proceeding to checkout...");
		setCheckoutProcessing(true);
		try {
			const { data, errors } = await checkoutMutation({
				variables: {
					input: {
						paymentMethod: "cod",
						billing: removeTypename(customer?.billing),
						shipping: removeTypename(customer?.shipping),
						clientMutationId: "asdasdsa241",
					},
				},
			});
			console.log("checkoutdata", data);
			return { success: data.success };
		} catch (error: any) {
			return { success: false, error: error.message || "Failed to checkout" };
		} finally {
			setCheckoutProcessing(false);
		}
	};
	if (isLoading && !cart) {
		return <LoadingSpinner />;
	}

	if (!cart || !cart.contents?.nodes || cart.contents.nodes.length === 0) {
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
			<h1 className="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

			<div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
				{/* Left Column - Customer Details */}
				<div className="bg-white rounded-lg shadow-sm border border-gray-200">
					<div className="p-6">
						<h3 className="text-xl font-semibold text-gray-900 mb-6">Customer Details</h3>

						{/* ✅ Show loading state */}
						{userDataLoading && <LoadingSpinner />}

						{/* ✅ Show login prompt if not authenticated */}
						{!isAuthenticated && !userDataLoading && (
							<div className="text-center py-8">
								<p className="text-gray-600 mb-4">Please log in to continue with checkout</p>
								<Link
									href="/account"
									className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors"
								>
									Log In
								</Link>
							</div>
						)}

						{/* ✅ Show addresses when customer data is loaded */}
						{isAuthenticated && !userDataLoading && customer && (
							<Addresses billing={customer.billing} shipping={customer.shipping} refetch={getCustomer} />
						)}

						{/* ✅ Show message if no customer data */}
						{isAuthenticated && !userDataLoading && !customer && (
							<p className="text-gray-500">Unable to load customer data</p>
						)}
					</div>
				</div>

				{/* Right Column - Order Summary & Cart Items */}
				<div className="lg:col-span-1 space-y-6">
					{/* Order Summary */}
					<div className="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-8">
						<div className="p-6">
							<h3 className="text-xl font-semibold text-gray-900 mb-6">Order Summary</h3>

							<div className="space-y-3">
								<div className="flex justify-between text-sm">
									<span className="text-gray-600">Subtotal:</span>
									<span className="font-medium text-gray-900">{cart.subtotal}</span>
								</div>

								{cart.discountTotal && cart.discountTotal !== "$0.00" && (
									<div className="flex justify-between text-sm">
										<span className="text-gray-600">Discount:</span>
										<span className="font-medium text-red-600">-{cart.discountTotal}</span>
									</div>
								)}

								{cart.shippingTotal && cart.shippingTotal !== "$0.00" && (
									<div className="flex justify-between text-sm">
										<span className="text-gray-600">Shipping:</span>
										<span className="font-medium text-gray-900">{cart.shippingTotal}</span>
									</div>
								)}

								{cart.totalTax && cart.totalTax !== "$0.00" && (
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
														disabled={isLoading}
														className="text-red-500 hover:text-red-700 font-bold text-xs w-5 h-5 rounded-full border border-red-300 hover:border-red-500 disabled:opacity-50"
														aria-label={`Remove coupon ${coupon.code}`}
													>
														×
													</button>
												</div>
											</div>
										))}
									</div>
								</div>
							)}

							{couponError && (
								<div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
									<div className="flex items-start">
										<svg
											className="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0"
											fill="currentColor"
											viewBox="0 0 20 20"
										>
											<path
												fillRule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
												clipRule="evenodd"
											/>
										</svg>
										<div className="flex-1">
											<p className="text-sm text-red-800 font-medium">{couponError}</p>
										</div>
										<button
											onClick={() => setCouponError(null)}
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
								</div>
							)}

							{couponSuccess && (
								<div className="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
									<div className="flex items-start">
										<svg
											className="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0"
											fill="currentColor"
											viewBox="0 0 20 20"
										>
											<path
												fillRule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
												clipRule="evenodd"
											/>
										</svg>
										<p className="text-sm text-green-800 font-medium flex-1">{couponSuccess}</p>
										<button
											onClick={() => setCouponSuccess(null)}
											className="text-green-500 hover:text-green-700 ml-2"
											aria-label="Dismiss success"
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
										disabled={applyingCoupon || !couponCode.trim()}
										className="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
									>
										{applyingCoupon ? "Applying..." : "Apply"}
									</button>
								</div>
							</form>

							{/* Checkout Button */}
							<button
								disabled={!isAuthenticated || !customer || checkoutProcessing}
								onClick={handleCheckout}
								className="mt-6 w-full bg-green-600 text-white text-center py-3 px-4 rounded-md hover:bg-green-700 transition-colors font-semibold text-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
							>
								Place Order
							</button>
						</div>
					</div>

					{/* Cart Items */}
					<div className="bg-white rounded-lg shadow-sm border border-gray-200">
						<div className="p-6">
							<h3 className="text-xl font-semibold text-gray-900 mb-6">Cart Items</h3>
							<div className="space-y-6">
								{cart.contents.nodes.map((item: CartItem) => {
									const product = item.product.node;
									const variation = item.variation?.node;
									const isUpdating = updatingItems[item.key];

									const imageUrl = variation?.image?.sourceUrl || product?.image?.sourceUrl || "/placeholder.jpg";
									const imageAlt =
										variation?.image?.altText || product?.image?.altText || product?.name || "Product image";

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
													src={imageUrl}
													alt={imageAlt}
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
												<div className="mt-1">
													{variation && (
														<p className="text-sm text-gray-500">
															{variation.attributes?.nodes?.map((attr) => attr.value).join(", ") || variation.name}
														</p>
													)}
													<p className="text-sm text-gray-700 mt-1">Subtotal: {item.subtotal}</p>
													{item.subtotalTax && item.subtotalTax !== "$0.00" && (
														<p className="text-xs text-gray-500">Tax: {item.subtotalTax}</p>
													)}
												</div>

												{/* Mobile Quantity and Remove */}
												<div className="flex items-center justify-between mt-4 sm:hidden">
													<div className="flex items-center border border-gray-300 rounded-md">
														<button
															onClick={() => handleUpdateQuantity(item.key, item.quantity - 1)}
															disabled={isUpdating || item.quantity <= 1}
															className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
															aria-label="Decrease quantity"
														>
															<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
																<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
															</svg>
														</button>
														<span className="px-3 py-2 text-sm font-medium text-gray-900 min-w-[50px] text-center">
															{item.quantity}
														</span>
														<button
															onClick={() => handleUpdateQuantity(item.key, item.quantity + 1)}
															disabled={isUpdating}
															className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
															aria-label="Increase quantity"
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
														onClick={() => handleUpdateQuantity(item.key, item.quantity - 1)}
														disabled={isUpdating || item.quantity <= 1}
														className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
														aria-label="Decrease quantity"
													>
														<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
															<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
														</svg>
													</button>
													<span className="px-4 py-2 text-sm font-medium text-gray-900 min-w-[60px] text-center">
														{item.quantity}
													</span>
													<button
														onClick={() => handleUpdateQuantity(item.key, item.quantity + 1)}
														disabled={isUpdating}
														className="p-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
														aria-label="Increase quantity"
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
			</div>
		</div>
	);
}
