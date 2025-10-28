import { gql } from "@apollo/client";

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

export const ProductVariationContentSlice = gql`
	fragment ProductVariationContentSlice on ProductVariation {
		id
		databaseId
		name
		slug
		image {
			id
			sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
			altText
		}
		price
		regularPrice
	}
`;

export const ProductContentFull = gql`
	fragment ProductContentFull on Product {
		id
		databaseId
		slug
		name
		type
		description
		shortDescription(format: RAW)
		image {
			id
			sourceUrl
			altText
		}
		galleryImages {
			nodes {
				id
				sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
				altText
			}
		}
		productTags(first: 20) {
			nodes {
				id
				slug
				name
			}
		}
		attributes {
			nodes {
				id
				attributeId
				... on LocalProductAttribute {
					name
					options
					variation
				}
				... on GlobalProductAttribute {
					name
					options
					variation
				}
			}
		}
		... on SimpleProduct {
			onSale
			stockStatus
			price
			rawPrice: price(format: RAW)
			regularPrice
			salePrice
			stockStatus
			stockQuantity
			soldIndividually
		}
		... on VariableProduct {
			onSale
			price
			rawPrice: price(format: RAW)
			regularPrice
			salePrice
			stockStatus
			stockQuantity
			soldIndividually
			variations(first: 50) {
				nodes {
					id
					databaseId
					name
					price
					rawPrice: price(format: RAW)
					regularPrice
					salePrice
					onSale
					attributes {
						nodes {
							name
							label
							value
						}
					}
				}
			}
		}
	}
`;

export const VariationContent = gql`
	fragment VariationContent on ProductVariation {
		id
		name
		slug
		price
		regularPrice
		salePrice
		stockStatus
		stockQuantity
		onSale
		image {
			id
			sourceUrl
			altText
		}
	}
`;

export const CartItemContent = gql`
	fragment CartItemContent on CartItem {
		key
		product {
			node {
				...ProductContentSlice
			}
		}
		variation {
			node {
				...ProductVariationContentSlice
			}
		}
		quantity
		total
		subtotal
		subtotalTax
		extraData {
			key
			value
		}
	}
	${ProductContentSlice}
	${ProductVariationContentSlice}
`;

export const CartContent = gql`
	fragment CartContent on Cart {
		isEmpty
		contents(first: 100) {
			itemCount
			nodes {
				...CartItemContent
			}
		}
		appliedCoupons {
			code
			discountAmount
			discountTax
		}
		needsShippingAddress
		availableShippingMethods {
			packageDetails
			supportsShippingCalculator
			rates {
				id
				instanceId
				methodId
				label
				cost
			}
		}
		subtotal
		subtotalTax
		shippingTax
		shippingTotal
		total
		totalTax
		feeTax
		feeTotal
		discountTax
		discountTotal
	}
	${CartItemContent}
`;

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

export const CustomerContent = gql`
	fragment CustomerContent on Customer {
		id
		sessionToken
	}
`;

// QUERIES
export const GetProduct = gql`
	query GetProduct($id: ID!, $idType: ProductIdTypeEnum) {
		product(id: $id, idType: $idType) {
			...ProductContentFull
		}
	}
	${ProductContentFull}
`;

export const GetProductVariation = gql`
	query GetProductVariation($id: ID!) {
		productVariation(id: $id, idType: DATABASE_ID) {
			...VariationContent
		}
	}
	${VariationContent}
`;

//CART
export const GetMiniCart = gql`
	query GetMiniCart {
		cart {
			subtotal
			totalTax
			total
			contents {
				itemCount
				nodes {
					key
					quantity
					subtotal
					subtotalTax
					total
					product {
						node {
							databaseId
							name
							slug
							featuredImage {
								node {
									sourceUrl(size: THUMBNAIL)
								}
							}
						}
					}
				}
			}
		}
	}
`;

// export const GetCart = gql`
//   query GetCart {
//     cart {
//       ...CartContent
//     }
//   }
//   ${CartContent}
// `;

// MUTATIONS
export const AddToCart = gql`
	mutation AddToCart($productId: Int!, $variationId: Int, $quantity: Int, $extraData: String) {
		addToCart(input: { productId: $productId, variationId: $variationId, quantity: $quantity, extraData: $extraData }) {
			cart {
				...CartContent
			}
			cartItem {
				...CartItemContent
			}
		}
	}
	${CartContent}
	${CartItemContent}
`;

export const UpdateCartItemQuantities = gql`
	mutation UpdateCartItemQuantities($items: [CartItemQuantityInput]!) {
		updateItemQuantities(input: { items: $items }) {
			cart {
				...CartContent
			}
			items {
				...CartItemContent
			}
		}
	}
	${CartContent}
	${CartItemContent}
`;

export const UpdateCartItemQuantity = gql`
	mutation UpdateCartItemQuantity($key: ID!, $quantity: Int!) {
		updateItemQuantities(input: { items: [{ key: $key, quantity: $quantity }] }) {
			cart {
				...CartContent
			}
			items {
				...CartItemContent
			}
		}
	}
	${CartContent}
	${CartItemContent}
`;

export const RemoveItemsFromCart = gql`
	mutation RemoveItemsFromCart($keys: [ID]!) {
		removeItemsFromCart(input: { keys: $keys }) {
			cart {
				...CartContent
			}
			cartItems {
				...CartItemContent
			}
		}
	}
	${CartContent}
	${CartItemContent}
`;

export const RemoveItemFromCart = gql`
	mutation RemoveItemFromCart($key: ID!) {
		removeItemsFromCart(input: { keys: [$key] }) {
			cart {
				...CartContent
			}
			cartItems {
				...CartItemContent
			}
		}
	}
	${CartContent}
	${CartItemContent}
`;

export const ClearCart = gql`
	mutation ClearCart {
		removeItemsFromCart(input: { all: true }) {
			cart {
				...CartContent
			}
		}
	}
	${CartContent}
`;

export const ApplyCoupon = gql`
	mutation ApplyCoupon($code: String!) {
		applyCoupon(input: { code: $code }) {
			cart {
				...CartContent
			}
		}
	}
	${CartContent}
`;

export const RemoveCoupons = gql`
	mutation RemoveCoupons($codes: [String]!) {
		removeCoupons(input: { codes: $codes }) {
			cart {
				...CartContent
			}
		}
	}
	${CartContent}
`;

export const UpdateCustomer = gql`
	mutation UpdateCustomer($input: UpdateCustomerInput!) {
		updateCustomer(input: $input) {
			customer {
				...CustomerFields
			}
		}
	}
	${CustomerFields}
`;
export const LOGIN_MUTATION = gql`
	mutation loginWithPassword($username: String!, $password: String!) {
		login(input: { provider: PASSWORD, credentials: { username: $username, password: $password } }) {
			authToken
			authTokenExpiration
			refreshToken
			refreshTokenExpiration
			user {
				id
				email
				databaseId
				name
			}
		}
	}
`;

export const REFRESH_TOKEN_MUTATION = gql`
	mutation refreshToken($token: String!) {
		refreshToken(input: { refreshToken: $token }) {
			authToken
			authTokenExpiration
			success
		}
	}
`;
