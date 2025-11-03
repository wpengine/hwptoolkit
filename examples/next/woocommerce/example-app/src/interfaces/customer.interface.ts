export interface Customer {
	id: string;
	databaseId: number;
	email: string;
	username: string;
	displayName: string;
	password?: string;
	password2?: string;
	firstName: string;
	lastName: string;
	billing: Address;
	shipping: Address;
	orders: UserOrders;
}
export interface UserOrders {
	nodes: Order[];
}

export interface Order {
	id: string;
	databaseId: number;
	date: string;
	dateCompleted: string | null;
	datePaid: string | null;
	orderNumber: string;
	orderVersion: string;
	paymentMethodTitle: string | null;
	needsProcessing: boolean;
	status: string;
	subtotal: string;
	total: string;
	totalTax: string;
	billing: Address;
	shipping: Address;
	lineItems: {
		__typename: string;
		nodes: LineItem[];
	};
}

export interface LineItem {
	id: string;
	productId: number;
	quantity: number;
	total: string;
}
export interface Address {
	firstName: string;
	lastName: string;
	company: string;
	address1: string;
	address2: string;
	city: string;
	state: string;
	country: string;
	postcode: string;
	phone: string;
}
