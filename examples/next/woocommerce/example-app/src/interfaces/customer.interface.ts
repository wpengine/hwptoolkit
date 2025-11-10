import { UserOrders } from "./order.interface";
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
