import React, { useState } from "react";
import UserField from "../../ui/Field";
import { UPDATE_CUSTOMER } from "@/lib/graphQL/userGraphQL";
import { useMutation } from "@apollo/client/react/hooks/useMutation";
import { FieldConfig, UserFieldsProps } from "@/interfaces/field.interface";

const userFieldsConfig: FieldConfig[] = [
	{ name: "firstName", label: "First Name", type: "text" },
	{ name: "lastName", label: "Last Name", type: "text" },
	{ name: "displayName", label: "Display Name", type: "text" },
	{ name: "email", label: "Email", type: "email" },
	{ name: "password", label: "Password", type: "password", placeholder: "Leave blank to keep current password" },
	{
		name: "password2",
		label: "Confirm Password",
		type: "password",
		placeholder: "Confirm Password if typed",
	},
];

export default function AccountDetails({ customer, onChange, refetch }: UserFieldsProps) {
	const [updateAccountMutation, { loading: updateLoading }] = useMutation(UPDATE_CUSTOMER);
	const [formData, setFormData] = useState({
		firstName: customer?.firstName || "",
		lastName: customer?.lastName || "",
		displayName: customer?.displayName || "",
		email: customer?.email || "",
		password: "",
		password2: "",
	});

	const [passwordError, setPasswordError] = useState("");

	const handleChange = (field: string, value: string) => {
		const updatedData = { ...formData, [field]: value };
		setFormData(updatedData);

		if (field === "password") {
			setPasswordError("");
		}
	};

	const handleUpdateCustomer = async (e: React.FormEvent) => {
		e.preventDefault();
		setPasswordError("");

		if (formData.password && formData.password !== formData.password2) {
			setPasswordError("Passwords do not match");
			return;
		}

		if (formData.password && formData.password.length < 8) {
			setPasswordError("Password must be at least 8 characters long");
			return;
		}

		try {
			const input: any = {
				id: customer.databaseId,
				email: formData.email,
				firstName: formData.firstName,
				lastName: formData.lastName,
				displayName: formData.displayName,
			};

			if (formData.password) {
				input.password = formData.password;
			}

			const result = await updateAccountMutation({
				variables: { input },
			});

			if (result.data) {
				alert("Account updated successfully!");
				if (refetch) {
					await refetch();
				}
				setFormData({ ...formData, password: "", password2: "" });
				onChange?.({ ...customer, ...result.data.updateUser.user });
			}
		} catch (err: any) {
			console.error("Error updating account:", err);
			alert(`There was an error updating your account: ${err.message}`);
		}
	};

	if (!customer) return null;

	return (
		<form onSubmit={handleUpdateCustomer} className="space-y-8">
			<h2 className="text-lg font-semibold mb-4">User Information</h2>

			<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
				{userFieldsConfig.map((field) => (
					<UserField
						key={field.name}
						name={field.name}
						label={field.label}
						type={field.type}
						value={formData[field.name as keyof typeof formData] || ""}
						onChange={(value) => handleChange(field.name, value)}
						readOnly={false}
						colSpan={field.colSpan}
						placeholder={field.placeholder ? field.placeholder : field.label}
					/>
				))}
			</div>

			{passwordError && (
				<div className="bg-red-50 border border-red-200 text-red-800 p-3 rounded-md text-sm">{passwordError}</div>
			)}

			<div className="bg-blue-50 border border-blue-200 text-blue-800 p-3 rounded-md text-sm">
				<p className="font-semibold mb-1">Password Policy:</p>
				<ul className="list-disc list-inside space-y-1">
					<li>Minimum 8 characters</li>
					<li>Leave blank to keep your current password</li>
				</ul>
			</div>

			<div className="flex justify-end space-x-4">
				<button
					type="button"
					onClick={() => {
						setFormData({
							firstName: customer?.firstName || "",
							lastName: customer?.lastName || "",
							displayName: customer?.displayName || "",
							email: customer?.email || "",
							password: "",
							password2: "",
						});
						setPasswordError("");
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
