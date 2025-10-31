import React from "react";

import { Customer } from "@/interfaces/customer.interface";

export default function BillingFields({ billing }: { billing: Customer["billing"] }) {
	if (!billing) return null;
	return (
		<div>
			<h2 className="text-lg font-semibold mb-4">Billing Information</h2>
			<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label className="block text-sm font-medium text-gray-700">First Name</label>
					<input
						type="text"
						value={billing?.firstName || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Last Name</label>
					<input
						type="text"
						value={billing?.lastName || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Company</label>
					<input
						type="text"
						value={billing?.company || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Address 1</label>
					<input
						type="text"
						value={billing?.address1 || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Address 2</label>
					<input
						type="text"
						value={billing?.address2 || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">City</label>
					<input
						type="text"
						value={billing?.city || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">State</label>
					<input
						type="text"
						value={billing?.state || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Country</label>
					<input
						type="text"
						value={billing?.country || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Postcode</label>
					<input
						type="text"
						value={billing?.postcode || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Phone</label>
					<input
						type="text"
						value={billing?.phone || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
			</div>
		</div>
	);
}
