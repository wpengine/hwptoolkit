import { Customer } from "@/interfaces/customer.interface";

export interface AddressesProps {
	billing: Customer["billing"];
	shipping: Customer["shipping"];
	refetch?: () => Promise<any>;
}
export interface UserFieldsProps {
	customer: Customer;
	onChange?: (customer: Customer) => void;
	readOnly?: boolean;
	refetch?: () => Promise<any>;
}

export interface FieldConfig {
	name: string;
	label: string;
	type?: string;
	colSpan?: string;
	placeholder?: string;
}
