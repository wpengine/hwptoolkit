import { useRouter } from "next/router";
import { useQuery } from "@apollo/client";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import OrderReceived from "@/components/OrderReceived/OrderReceived";
import Link from "next/link";
import { Order } from "@/interfaces/order.interface.ts";
import { formatDate } from "@/lib/utils";
import OrderItem from "@/components/OrderReceived/OrderItem";

export default function SingleOrder(orderObj: Order) {
	const order = orderObj.order;
	const formatOrderStatus = (status: string) => {
		return status.toLowerCase().replace(/_/g, "");
	};
	return (
		<div className="min-h-screen bg-gray-50 py-8">
			<h1 className="text-2xl font-semibold text-gray-900 mb-4">
				Order #{order.orderNumber} - {order.status}
			</h1>

			<div className="border-b border-gray-200">
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
			<div className="mt-4 border-b border-gray-200">
				<h3 className="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
				<div className="space-y-3">
					{order.lineItems?.nodes?.map((item) => (
						<OrderItem key={item.id} item={item} />
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
			<div className="pt-6">
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
								Name: {order.billing.firstName} {order.billing.lastName}
							</p>
							{order.billing.email && <p className="pt-2">Email: {order.billing.email}</p>}
							{order.billing.phone && <p>Phone: {order.billing.phone}</p>}
							<p>Address: {order.billing.address1}</p>
							{order.billing.address2 && <p>{order.billing.address2}</p>}
							<p>
								City: {order.billing.city}, State: {order.billing.state} Postcode: {order.billing.postcode}
							</p>
							<p>Country: {order.billing.country}</p>
						</div>
					</div>

					{/* Shipping Address */}
					{order.shipping && (
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
					)}
				</div>
			</div>
		</div>
	);
}
