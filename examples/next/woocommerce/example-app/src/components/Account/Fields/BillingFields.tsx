import React, { useState } from "react";
import { Customer } from "@/interfaces/customer.interface";
import UserField from "@/components/ui/Field";

interface BillingFieldsProps {
	billing: Customer["billing"];
	onChange?: (billing: Customer["billing"]) => void;
	readOnly?: boolean;
}

interface FieldConfig {
	name: keyof Customer["billing"];
	label: string;
	type?: string;
	colSpan?: string;
}

const billingFieldsConfig: FieldConfig[] = [
	{ name: "firstName", label: "First Name", type: "text" },
	{ name: "lastName", label: "Last Name", type: "text" },
	{ name: "company", label: "Company", type: "text" },
	{ name: "phone", label: "Phone", type: "tel" },
	{ name: "address1", label: "Address 1", type: "text" },
	{ name: "address2", label: "Address 2", type: "text" },
	{ name: "city", label: "City", type: "text" },
	{ name: "state", label: "State", type: "text" },
	{ name: "postcode", label: "Postcode", type: "text" },
	{ name: "country", label: "Country", type: "text" },
];

export default function BillingFields({ billing, onChange, readOnly = false }: BillingFieldsProps) {
	const [formData, setFormData] = useState(
		billing || {
			firstName: "",
			lastName: "",
			company: "",
			address1: "",
			address2: "",
			city: "",
			state: "",
			country: "",
			postcode: "",
			phone: "",
			email: "",
		}
	);

	const handleChange = (field: string, value: string) => {
		const updatedData = { ...formData, [field]: value };
		setFormData(updatedData);
		onChange?.(updatedData);
	};

	if (!billing) return null;

	return (
		<div>
			<h2 className="text-lg font-semibold mb-4">Billing Information</h2>
			<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
				{billingFieldsConfig.map((field) => (
					<UserField
						key={field.name}
						name={field.name}
						label={field.label}
						type={field.type}
						value={formData?.[field.name] || ""}
						onChange={(value) => handleChange(field.name, value)}
						readOnly={readOnly}
						colSpan={field.colSpan}
					/>
				))}
			</div>
		</div>
	);
}
