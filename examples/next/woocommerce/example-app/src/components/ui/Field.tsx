import React from "react";

interface UserFieldProps {
	name: string;
	label: string;
	type?: string;
	value: string;
	onChange: (value: string) => void;
	readOnly?: boolean;
	colSpan?: string;
	required?: boolean;
	placeholder?: string;
}

export default function UserField({
	name,
	label,
	type = "text",
	value,
	onChange,
	readOnly = false,
	colSpan = "",
	required = false,
	placeholder,
}: UserFieldProps) {
	return (
		<div className={colSpan}>
			<label htmlFor={name} className="block text-sm font-medium text-gray-700">
				{label}
				{required && <span className="text-red-500 ml-1">*</span>}
			</label>
			<input
				id={name}
				name={name}
				type={type}
				value={value}
				onChange={(e) => onChange(e.target.value)}
				readOnly={readOnly}
				required={required}
				placeholder={placeholder}
				className={`mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-blue-500 focus:border-blue-500 ${
					readOnly ? "bg-gray-50 cursor-not-allowed" : "bg-white"
				}`}
			/>
		</div>
	);
}
