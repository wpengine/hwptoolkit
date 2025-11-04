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
			sourceUrl(size: THUMBNAIL)
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
			sourceUrl(size: THUMBNAIL)
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
export const UPDATE_ITEM_QUANTITIES = gql`
	mutation UpdateItemQuantities($input: UpdateItemQuantitiesInput!) {
		updateItemQuantities(input: $input) {
			updated {
				key
				quantity
				total
				subtotal
			}
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
	}
`;
export const REMOVE_ITEMS_FROM_CART = gql`
  mutation RemoveItemsFromCart($input: RemoveItemsFromCartInput!) {
    removeItemsFromCart(input: $input) {
      cartItems {
        key
        quantity
      }
      cart {
        contents {
          nodes {
            key
            quantity
            total
            subtotal
          }
        }
        subtotal
        total
        totalTax
        contentsTotal
        contentsTax
      }
    }
  }
`;
//CART
export const GET_CART = gql`
	query GetCart {
		cart {
			contents {
				itemCount
				productCount
				nodes {
					key
					quantity
					total
					subtotal
					subtotalTax
					product {
						node {
							id
							databaseId
							name
							slug
							type
							image {
								id
								sourceUrl(size: THUMBNAIL)
								altText
							}
						}
					}
					variation {
						node {
							id
							databaseId
							name
							image {
								sourceUrl
								altText
							}
							attributes {
								nodes {
									name
									value
								}
							}
						}
					}
				}
			}
			appliedCoupons {
				code
				discountAmount
				description
			}
			subtotal
			subtotalTax
			shippingTotal
			shippingTax
			discountTotal
			discountTax
			total
			totalTax
			feeTax
			feeTotal
		}
	}
`;
export const GET_MINI_CART = gql`
	query GetMiniCart {
		cart {
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
							name
							image {
								id
								sourceUrl(size: THUMBNAIL)
								altText
							}
						}
					}
				}
			}
			subtotal
			totalTax
			total
		}
	}
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

export const EMPTY_CART = gql`
    mutation EmptyCart {
        emptyCart(input: { clearPersistentCart: true }) {
            cart {
                contents {
                    itemCount
                }
            }
        }
    }
`;

export const APPLY_COUPON = gql`
	mutation ApplyCoupon($code: String!) {
		applyCoupon(input: { code: $code }) {
			cart {
				...CartContent
			}
		}
	}
	${CartContent}
`;

export const REMOVE_COUPONS = gql`
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
export const CHECKOUT_MUTATION = gql`
	mutation checkout($input: CheckoutInput!) {
		checkout(input: $input) {
			order {
				...OrderFields
			}
			redirect
			result
			clientMutationId
		}
	}
	${OrderFields}
`;