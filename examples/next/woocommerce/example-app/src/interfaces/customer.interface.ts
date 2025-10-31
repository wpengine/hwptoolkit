export interface Customer {
    id: string;
    email: string;
    username: string;
    billing: Address;
    shipping: Address;
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