import React from "react";
import Link from "next/link";
import { Order } from "@/interfaces/order.interface";

export default function OrderReceived({ order }: { order: Order | null }) {
	console.log("order", order);
	if (!order) {
		return (
			<div className="max-w-4xl mx-auto px-4 py-16 text-center">
				<div className="bg-red-50 border border-red-200 rounded-lg p-8">
					<svg className="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth={2}
							d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
						/>
					</svg>
					<h2 className="text-2xl font-bold text-red-800 mb-2">Order Not Found</h2>
					<p className="text-red-600 mb-6">We couldn't find your order details.</p>
					<Link
						href="/shop"
						className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium"
					>
						Continue Shopping
					</Link>
				</div>
			</div>
		);
	}

	const formatDate = (dateString: string | null) => {
		if (!dateString) return "N/A";
		return new Date(dateString).toLocaleDateString("en-US", {
			year: "numeric",
			month: "long",
			day: "numeric",
			hour: "2-digit",
			minute: "2-digit",
		});
	};

	const getStatusColor = (status: string) => {
		const colors: Record<string, string> = {
			completed: "bg-green-100 text-green-800 border-green-300",
			processing: "bg-blue-100 text-blue-800 border-blue-300",
			pending: "bg-yellow-100 text-yellow-800 border-yellow-300",
			onhold: "bg-orange-100 text-orange-800 border-orange-300",
			cancelled: "bg-red-100 text-red-800 border-red-300",
			refunded: "bg-gray-100 text-gray-800 border-gray-300",
			failed: "bg-red-100 text-red-800 border-red-300",
		};
		return colors[status] || "bg-gray-100 text-gray-800 border-gray-300";
	};
	const formatOrderStatus = (status: string) => {
		return status.toLowerCase().replace(/_/g, "");
	};
	if (order) {
		console.log("formatOrderStatus", order.status);
		order.status = formatOrderStatus(order.status);
	}

	return (
		<div className="max-w-4xl mx-auto px-4 py-8">
			{/* Success Header */}
			<div className="text-center mb-8">
				<div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
					<svg className="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
					</svg>
				</div>
				<h1 className="text-3xl font-bold text-gray-900 mb-2">Thank You for Your Order!</h1>
				<p className="text-gray-600 text-lg">Your order has been received and is being processed.</p>
			</div>

			{/* Order Details Card */}
			<div className="bg-white rounded-lg shadow-md border border-gray-200 mb-6">
				<div className="p-6 border-b border-gray-200">
					<div className="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div>
							<h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Order Number</h3>
							<p className="text-2xl font-bold text-gray-900">#{order.orderNumber}</p>
						</div>
						<div>
							<h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Order Status</h3>
							<span
								className={`inline-block px-4 py-2 rounded-full text-sm font-semibold border ${getStatusColor(
									order.status
								)}`}
							>
								{order.status.charAt(0).toUpperCase() + order.status.slice(1)}
							</span>
						</div>
					</div>
				</div>

				<div className="p-6 border-b border-gray-200">
					<div className="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div>
							<h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Order Date</h3>
							<p className="text-gray-900">{formatDate(order.date)}</p>
						</div>
						<div>
							<h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Payment Method</h3>
							<p className="text-gray-900">{order.paymentMethodTitle || "N/A"}</p>
						</div>
						<div>
							<h3 className="text-sm font-semibold text-gray-500 uppercase mb-2">Total Amount</h3>
							<p className="text-2xl font-bold text-green-600">{order.total}</p>
						</div>
					</div>
				</div>

				{/* Order Items */}
				<div className="p-6 border-b border-gray-200">
					<h3 className="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
					<div className="space-y-3">
						{order.lineItems?.nodes?.map((item) => (
							<div
								key={item.id}
								className="flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0"
							>
								<div className="flex-1">
									<p className="font-medium text-gray-900">Product ID: {item.productId}</p>
									<p className="text-sm text-gray-600">Quantity: {item.quantity}</p>
								</div>
								<div className="text-right">
									<p className="font-semibold text-gray-900">{item.total}</p>
								</div>
							</div>
						))}
					</div>

					{/* Order Totals */}
					<div className="mt-6 pt-4 border-t border-gray-200">
						<div className="space-y-2">
							<div className="flex justify-between text-sm">
								<span className="text-gray-600">Subtotal:</span>
								<span className="font-medium text-gray-900">{order.subtotal}</span>
							</div>
							{order.totalTax && order.totalTax !== "$0.00" && (
								<div className="flex justify-between text-sm">
									<span className="text-gray-600">Tax:</span>
									<span className="font-medium text-gray-900">{order.totalTax}</span>
								</div>
							)}
							<div className="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
								<span className="text-gray-900">Total:</span>
								<span className="text-green-600">{order.total}</span>
							</div>
						</div>
					</div>
				</div>

				{/* Addresses */}
				<div className="p-6">
					<div className="grid grid-cols-1 md:grid-cols-2 gap-6">
						{/* Billing Address */}
						<div>
							<h3 className="text-lg font-semibold text-gray-900 mb-3 flex items-center">
								<svg className="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										strokeWidth={2}
										d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
									/>
								</svg>
								Billing Address
							</h3>
							<div className="text-sm text-gray-700 space-y-1">
								<p className="font-medium">
									{order.billing.firstName} {order.billing.lastName}
								</p>
								<p>{order.billing.address1}</p>
								{order.billing.address2 && <p>{order.billing.address2}</p>}
								<p>
									{order.billing.city}, {order.billing.state} {order.billing.postcode}
								</p>
								<p>{order.billing.country}</p>
								{order.billing.email && <p className="pt-2">Email: {order.billing.email}</p>}
								{order.billing.phone && <p>Phone: {order.billing.phone}</p>}
							</div>
						</div>

						{/* Shipping Address */}
						<div>
							<h3 className="text-lg font-semibold text-gray-900 mb-3 flex items-center">
								<svg className="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										strokeWidth={2}
										d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
									/>
								</svg>
								Shipping Address
							</h3>
							<div className="text-sm text-gray-700 space-y-1">
								<p className="font-medium">
									{order.shipping.firstName} {order.shipping.lastName}
								</p>
								<p>{order.shipping.address1}</p>
								{order.shipping.address2 && <p>{order.shipping.address2}</p>}
								<p>
									{order.shipping.city}, {order.shipping.state} {order.shipping.postcode}
								</p>
								<p>{order.shipping.country}</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			{/* Action Buttons */}
			<div className="flex flex-col sm:flex-row gap-4 justify-center">
				<Link
					href="/account/orders"
					className="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium"
				>
					<svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth={2}
							d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
						/>
					</svg>
					View All Orders
				</Link>
				<Link
					href="/shop"
					className="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors font-medium"
				>
					<svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth={2}
							d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
						/>
					</svg>
					Continue Shopping
				</Link>
			</div>

			{/* Additional Info */}
			<div className="mt-8 p-6 bg-blue-50 border border-blue-200 rounded-lg">
				<div className="flex items-start">
					<svg className="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
						<path
							fillRule="evenodd"
							d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
							clipRule="evenodd"
						/>
					</svg>
					<div>
						<h4 className="font-semibold text-blue-900 mb-1">What's Next?</h4>
						<p className="text-sm text-blue-800">
							You will receive an email confirmation shortly with your order details. We'll notify you when your order
							ships. You can track your order status in the{" "}
							<Link href="/account/orders" className="underline font-medium hover:text-blue-900">
								Orders section
							</Link>{" "}
							of your account.
						</p>
					</div>
				</div>
			</div>
		</div>
	);
}
