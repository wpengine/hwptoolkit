import { gql } from "@apollo/client";

export const GET_ORDER = gql`
	query GetOrder($orderId: ID!, $idType: OrderIdTypeEnum!) {
		order(id: $orderId, idType: $idType) {
			id
			databaseId
			orderNumber
			orderKey
			status
			date
			total
			subtotal
			totalTax
			paymentMethodTitle
			billing {
				firstName
				lastName
				address1
				address2
				city
				state
				postcode
				country
				email
				phone
			}
			shipping {
				firstName
				lastName
				address1
				address2
				city
				state
				postcode
				country
			}
			lineItems {
				nodes {
					id
					databaseId
					productId
					quantity
					total
					subtotal

					product {
						node {
							slug
							... on SimpleProduct {
								id
								databaseId
								name
								image {
									id
									sourceUrl
									altText
								}
							}
							... on VariableProduct {
								id
								databaseId
								name
								image {
									id
									sourceUrl
									altText
								}
							}
						}
					}
				}
			}
		}
	}
`;
