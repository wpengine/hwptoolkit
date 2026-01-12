import { useRouter } from "next/router";
import { useEffect, useState } from "react";
import { useQuery } from "@apollo/client";
import OrderReceived from "@/components/OrderReceived/OrderReceived";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
import { GET_ORDER } from "@/lib/graphQL/orderGraphQL";
import useLocalStorage from "@/lib/storage";

export default function OrderReceivedPage() {
	const router = useRouter();
	const { key } = router.query;
	const { user, isLoading: authLoading } = useAuthAdmin();
	const isAuthenticated = !!user;
	const storage = useLocalStorage;
	const [orderData, setOrderData] = useState(null);
	const [skipQuery, setSkipQuery] = useState(false);

	useEffect(() => {
		if (!isAuthenticated && key && typeof window !== "undefined") {
			const storedData = storage.getItem(`order_${key}`);
			if (storedData) {
				try {
					const parsed = JSON.parse(storedData);
					const order = parsed.order || parsed;

					setOrderData(order);
					setSkipQuery(true);
				} catch (error) {
					setSkipQuery(false);
				}
			}
		}
	}, [key, isAuthenticated, storage]);

	const {
		data,
		loading: orderDataLoading,
		error,
	} = useQuery(GET_ORDER, {
		variables: {
			orderId: key,
			idType: "ORDER_KEY",
		},
		skip: !key || skipQuery || (!isAuthenticated && orderData !== null),
		fetchPolicy: "network-only",
	});

	useEffect(() => {
		if (data?.order) {
			setOrderData(data.order);
		}
	}, [data]);

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

	if (authLoading || (orderDataLoading && !orderData)) {
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
