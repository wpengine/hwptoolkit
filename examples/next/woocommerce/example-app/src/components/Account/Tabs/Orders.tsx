import React, { useEffect, useState } from "react";
import { UserOrders, Order } from "@/interfaces/customer.interface";
import { formatDate } from "@/lib/utils";
import Link from "next/link";
export default function Orders({ orders }: { orders: UserOrders }) {
	const [orderTotal, setOrderTotal] = useState(0);
	const [filteredOrders, setFilteredOrders] = useState<Order[]>([]);

	useEffect(() => {
		if (!orders) return;

		const filtered = orders.filter((order: Order) => order.status !== "CHECKOUT_DRAFT");
		setFilteredOrders(filtered);
		setOrderTotal(filtered.length);
	}, [orders]);

	if (!orders) {
		return <div>No orders found.</div>;
	}
	return (
		<div className="orders-container">
			{orderTotal > 0 && <h4 className="text-2xl font-semibold mb-4">Orders ({orderTotal})</h4>}
			<table className="min-w-full border-collapse border border-gray-300">
				<thead className="bg-gray-100">
					<tr>
						<th className="border border-gray-300 px-4 py-2 text-left">ID</th>
						<th className="border border-gray-300 px-4 py-2 text-left">Date</th>
						<th className="border border-gray-300 px-4 py-2 text-left">Status</th>
						<th className="border border-gray-300 px-4 py-2 text-left">Total</th>
					</tr>
				</thead>
				<tbody>
					{filteredOrders.map((order: Order) => (
						<tr key={order.id} className="hover:bg-gray-50">
							<td className="border border-gray-300 px-4 py-2"><Link href={`my-account/view-order/${order.databaseId}`}>{order.databaseId}</Link></td>
							<td className="border border-gray-300 px-4 py-2">{formatDate(order.date)}</td>
							<td className="border border-gray-300 px-4 py-2">{order.status}</td>
							<td className="border border-gray-300 px-4 py-2">{order.total}</td>
						</tr>
					))}
				</tbody>
			</table>
		</div>
	);
}
