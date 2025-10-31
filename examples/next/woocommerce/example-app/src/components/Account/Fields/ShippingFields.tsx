import React from "react";

import { Customer } from "@/interfaces/customer.interface";

export default function ShippingFields({ shipping }: { shipping: Customer["shipping"] }) {
	return (
		<div>
			<h2 className="text-lg font-semibold mb-4">Shipping Information</h2>
			<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label className="block text-sm font-medium text-gray-700">First Name</label>
					<input
						type="text"
						value={shipping?.firstName || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Last Name</label>
					<input
						type="text"
						value={shipping?.lastName || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Company</label>
					<input
						type="text"
						value={shipping?.company || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Address 1</label>
					<input
						type="text"
						value={shipping?.address1 || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Address 2</label>
					<input
						type="text"
						value={shipping?.address2 || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">City</label>
					<input
						type="text"
						value={shipping?.city || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">State</label>
					<input
						type="text"
						value={shipping?.state || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Country</label>
					<input
						type="text"
						value={shipping?.country || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Postcode</label>
					<input
						type="text"
						value={shipping?.postcode || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
				<div>
					<label className="block text-sm font-medium text-gray-700">Phone</label>
					<input
						type="text"
						value={shipping?.phone || ""}
						className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
					/>
				</div>
			</div>
		</div>
	);
}
