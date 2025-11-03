import { Customer } from "@/interfaces/customer.interface";

export interface UserFieldsProps {
	customer: Customer;
	onChange?: (customer: Customer) => void;
	readOnly?: boolean;
}

export interface FieldConfig {
	name: string;
	label: string;
	type?: string;
	colSpan?: string;
	placeholder?: string;
}
