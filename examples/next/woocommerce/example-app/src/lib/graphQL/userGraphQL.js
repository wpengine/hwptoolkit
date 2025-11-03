import { gql } from "@apollo/client";

// ✅ Define fragments first, before they're used
export const AddressFields = gql`
    fragment AddressFields on CustomerAddress {
        firstName
        lastName
        company
        address1
        address2
        city
        state
        country
        postcode
        phone
    }
`;

export const ProductContentSlice = gql`
    fragment ProductContentSlice on Product {
        id
        databaseId
        name
        slug
        type
        image {
            id
            sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
            altText
        }
        ... on SimpleProduct {
            price
            regularPrice
            soldIndividually
        }
        ... on VariableProduct {
            price
            regularPrice
            soldIndividually
        }
    }
`;

export const LineItemFields = gql`
    fragment LineItemFields on LineItem {
        databaseId
        product {
            node {
                ...ProductContentSlice
            }
        }
        orderId
        quantity
        subtotal
        total
        totalTax
    }
    ${ProductContentSlice}
`;

export const OrderFields = gql`
    fragment OrderFields on Order {
        id
        databaseId
        orderNumber
        orderVersion
        status
        needsProcessing
        subtotal
        paymentMethodTitle
        total
        totalTax
        date
        dateCompleted
        datePaid
        billing {
            ...AddressFields
        }
        shipping {
            ...AddressFields
        }
        lineItems(first: 100) {
            nodes {
                ...LineItemFields
            }
        }
    }
    ${AddressFields}
    ${LineItemFields}
`;

export const CustomerFields = gql`
    fragment CustomerFields on Customer {
        id
        databaseId
        firstName
        lastName
        displayName
        email
        username
        billing {
            ...AddressFields
        }
        shipping {
            ...AddressFields
        }
        orders(first: 100) {
            nodes {
                ...OrderFields
            }
        }
    }
    ${AddressFields}
    ${OrderFields}
`;

// ✅ MUTATIONS
export const UPDATE_CUSTOMER = gql`
    mutation UpdateCustomer($input: UpdateCustomerInput!) {
        updateCustomer(input: $input) {
            customer {
                ...CustomerFields
            }
        }
    }
    ${CustomerFields}
`;

// ✅ QUERIES
export const GET_USER_SETTINGS = gql`
    query GetUserSettings {
        customer {
            ...CustomerFields
        }
    }
    ${CustomerFields}
`;

export const GET_USER = gql`
    query GetUser {
        viewer {
            ...CustomerFields
        }
    }
    ${CustomerFields}
`;