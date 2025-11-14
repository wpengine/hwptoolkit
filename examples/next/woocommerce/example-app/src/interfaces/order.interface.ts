import { Address } from "./customer.interface";
import { CartProduct } from "./cart.interface";
export interface UserOrders {
	nodes: Order[];
}

export interface Order {
	id: string;
	databaseId: number;
	date: string;
	dateCompleted?: string | null;
	datePaid?: string | null;
	orderNumber: string;
	orderKey?: string;
	orderVersion?: string;
	paymentMethodTitle?: string | null;
	needsProcessing?: boolean;
	status: string;
	subtotal: string;
	total: string;
	totalTax?: string;
	billing: Address;
	shipping: Address;
	lineItems: {
		__typename?: string;
		nodes: LineItem[];
	};
}

export interface LineItem {
	id: string;
	databaseId: number;
	product: CartProduct;
	quantity: number;
	total: string;
	subtotal?: string; // ✅ Added for subtotal
}
