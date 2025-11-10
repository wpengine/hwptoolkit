import { useRouter } from "next/router";
import { useEffect, useState } from "react";
import { gql, useQuery } from "@apollo/client";
import OrderReceived from "@/components/Checkout/OrderReceived";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
//import { Order } from "@/interfaces/order.interface";
import useLocalStorage from "@/lib/storage";

const GET_ORDER = gql`
	query GetOrder($orderId: ID!, $idType: OrderIdTypeEnum!) {
		order(id: $orderId, idType: $idType) {
			id
			databaseId
			orderNumber
			orderKey
			status
			date
			total
			subtotal
			totalTax
			paymentMethodTitle
			billing {
				firstName
				lastName
				address1
				address2
				city
				state
				postcode
				country
				email
				phone
			}
			shipping {
				firstName
				lastName
				address1
				address2
				city
				state
				postcode
				country
			}
			lineItems {
				nodes {
					id
					productId
					quantity
					total
					subtotal
				}
			}
		}
	}
`;

export default function OrderReceivedPage() {
	const router = useRouter();
	const { orderNumber, key } = router.query;
	const { user } = useAuthAdmin();
	const isAuthenticated = !!user;
	const storage = useLocalStorage;
	const [orderData, setOrderData] = useState(null);
	const [useSessionData, setUseSessionData] = useState(false);

	// ✅ For authenticated users, fetch from GraphQL
	const { data, loading, error } = useQuery(GET_ORDER, {
		variables: {
			orderId: key,
			idType: "ORDER_KEY",
		},
	});
	// ✅ Set order data from GraphQL for authenticated users
	useEffect(() => {
		if (isAuthenticated && data?.order) {
			setOrderData(data.order);
		} else {
			let orderData = storage.getItem(`order_${key}`);
			orderData = JSON.parse(orderData);
			orderData = orderData.order;
			console.log(orderData);
			if (orderData) {
				setOrderData(orderData);
			}
		}
	}, [data, isAuthenticated, key, storage]);

	if (!key) {
		return (
			<div className="max-w-4xl mx-auto px-4 py-16 text-center">
				<div className="bg-red-50 border border-red-200 rounded-lg p-8">
					<h2 className="text-2xl font-bold text-red-800 mb-2">Invalid Order Link</h2>
					<p className="text-red-600">Please check your order confirmation email for the correct link.</p>
				</div>
			</div>
		);
	}

	if (loading && !orderData) {
		return <LoadingSpinner />;
	}

	if (error && !orderData) {
		return (
			<div className="max-w-4xl mx-auto px-4 py-16 text-center">
				<div className="bg-red-50 border border-red-200 rounded-lg p-8">
					<h2 className="text-2xl font-bold text-red-800 mb-2">Error Loading Order</h2>
					<p className="text-red-600">{error.message}</p>
				</div>
			</div>
		);
	}

	return <OrderReceived order={orderData} />;
}
