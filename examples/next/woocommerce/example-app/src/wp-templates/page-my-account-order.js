import SingleOrder from "@/components/Account/Tabs/SingleOrder";
import { GET_ORDER } from "@/lib/graphQL/orderGraphQL";
import { useRouter } from "next/router";
import { useState, useEffect } from "react";
import { useQuery } from "@apollo/client";
import { useAuthAdmin } from "@/lib/providers/AuthProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import Link from "next/link";

export default function PageMyAccountOrder() {
	const router = useRouter();
	const { uri } = router.query;

	const orderId = uri && Array.isArray(uri) ? uri[uri.length - 1] : null;

	const { user, isLoading: authLoading } = useAuthAdmin();
	const isAuthenticated = !!user;

	const {
		data,
		loading: orderLoading,
		error,
	} = useQuery(GET_ORDER, {
		skip: !isAuthenticated || !orderId,
		variables: {
			orderId: parseInt(orderId, 10),
			idType: "DATABASE_ID",
		},
		fetchPolicy: "network-only",
	});

	if (authLoading || orderLoading) {
		return <LoadingSpinner />;
	}

	if (!isAuthenticated) {
		return (
			<div className="max-w-4xl mx-auto px-4 py-16 text-center">
				<div className="bg-yellow-50 border border-yellow-200 rounded-lg p-8">
					<h2 className="text-2xl font-bold text-yellow-800 mb-2">Authentication Required</h2>
					<p className="text-yellow-600 mb-6">Please log in to view your order.</p>
					<Link
						href="/my-account"
						className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium"
					>
						Go to Login
					</Link>
				</div>
			</div>
		);
	}


	if (!data?.order) {
		return (
			<div className="max-w-4xl mx-auto px-4 py-16 text-center">
				<div className="bg-red-50 border border-red-200 rounded-lg p-8">
					<h2 className="text-2xl font-bold text-red-800 mb-2">Order Not Found</h2>
					<p className="text-red-600 mb-6">
						The order you're looking for doesn't exist or you don't have permission to view it.
					</p>
					<Link
						href="/my-account"
						className="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium"
					>
						Back to My Account
					</Link>
				</div>
			</div>
		);
	}

	return (
		<div className="min-h-screen bg-gray-50 py-8">
			<div className="max-w-4xl mx-auto px-4">
				{/* Breadcrumb */}
				<nav className="mb-6 text-sm">
					<ol className="flex items-center space-x-2 text-gray-600">
						<li>
							<Link href="/" className="hover:text-blue-600">
								Home
							</Link>
						</li>
						<li>/</li>
						<li>
							<Link href="/my-account" className="hover:text-blue-600">
								My Account
							</Link>
						</li>
						<li>/</li>
						<li className="text-gray-900 font-medium">Order #{data.order.databaseId}</li>
					</ol>
				</nav>

				{/* Order Details */}
				<SingleOrder order={data.order} />

				{/* Back Button */}
				<div className="mt-8 text-center">
					<Link href="/my-account" className="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium">
						<svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
						</svg>
						Back to My Account
					</Link>
				</div>
			</div>
		</div>
	);
}
