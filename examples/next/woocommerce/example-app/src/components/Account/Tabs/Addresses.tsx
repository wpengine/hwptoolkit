import React, { useState, useEffect } from "react";
import { UPDATE_CUSTOMER } from "@/lib/graphQL/userGraphQL";
import BillingFields from "../Fields/BillingFields";
import ShippingFields from "../Fields/ShippingFields";
import { Customer } from "@/interfaces/customer.interface";
import { useMutation } from "@apollo/client/react/hooks/useMutation";

interface AddressesProps {
	billing: Customer["billing"];
	shipping: Customer["shipping"];
	refetch?: () => Promise<any>;
}

export default function Addresses({ billing: initialBilling, shipping: initialShipping, refetch }: AddressesProps) {
	const [billingData, setBillingData] = useState<Customer["billing"]>(initialBilling || {});
	const [shippingData, setShippingData] = useState<Customer["shipping"]>(initialShipping || {});
	const [updateAccountMutation, { loading: updateLoading }] = useMutation(UPDATE_CUSTOMER);

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

	useEffect(() => {
		if (initialBilling) {
			setBillingData(removeTypename(initialBilling));
		}
		if (initialShipping) {
			setShippingData(removeTypename(initialShipping));
		}
	}, [initialBilling, initialShipping]);

	const handleUpdateCustomer = async (e: React.FormEvent) => {
		e.preventDefault();

		try {
			const cleanBilling = removeTypename(billingData);
			const cleanShipping = removeTypename(shippingData);

			const result = await updateAccountMutation({
				variables: {
					input: {
						billing: cleanBilling,
						shipping: cleanShipping,
					},
				},
			});

			console.log("Update result:", result);

			if (result.data) {
				alert("Account updated successfully!");
				if (refetch) {
					await refetch();
				}
			}
		} catch (err: any) {
			console.error("Error updating account:", err);
			alert(`There was an error updating your account: ${err.message}`);
		}
	};

	return (
		<form onSubmit={handleUpdateCustomer} className="space-y-8">
			<div className="bg-white p-6 rounded-lg shadow">
				<BillingFields billing={billingData} onChange={setBillingData} readOnly={false} />
			</div>

			<div className="bg-white p-6 rounded-lg shadow">
				<ShippingFields shipping={shippingData} onChange={setShippingData} readOnly={false} />
			</div>

			<div className="flex justify-end space-x-4">
				<button
					type="button"
					onClick={() => {
						setBillingData(removeTypename(initialBilling || {}));
						setShippingData(removeTypename(initialShipping || {}));
					}}
					className="py-3 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
				>
					Reset Changes
				</button>
				<button
					type="submit"
					disabled={updateLoading}
					className="py-3 px-6 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
				>
					{updateLoading ? "Updating..." : "Update Account"}
				</button>
			</div>
		</form>
	);
}
