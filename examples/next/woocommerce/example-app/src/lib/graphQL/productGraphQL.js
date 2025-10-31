import { gql } from "@apollo/client";

export const PRODUCTS_QUERY = gql`
	query ProductDisplayQueries {
		# Recent Products (by date)
		RecentProducts: products(first: 50, where: { orderby: { field: DATE, order: DESC } }) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				description
				shortDescription
				sku
				reviewCount
				averageRating
				onSale
				image {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}
				galleryImages {
					nodes {
						id
						sourceUrl
						altText
						mediaDetails {
							width
							height
						}
					}
				}
				productCategories {
					nodes {
						id
						name
						slug
					}
				}
				productTags {
					nodes {
						id
						name
						slug
					}
				}

				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
					stockQuantity
					weight
					length
					width
					height
					totalSales
					attributes {
						nodes {
							id
							name
							options
						}
					}
				}

				... on VariableProduct {
					price
					regularPrice
					salePrice
					stockStatus
					stockQuantity
					weight
					length
					width
					height
					totalSales
					attributes {
						nodes {
							id
							name
							options
						}
					}
					variations {
						nodes {
							id
							name
							price
							regularPrice
							salePrice
							stockStatus
							stockQuantity
						}
					}
				}

				... on ExternalProduct {
					price
					regularPrice
					salePrice
					externalUrl
					buttonText
					attributes {
						nodes {
							id
							name
							options
						}
					}
				}

				... on GroupProduct {
					price
					regularPrice
					salePrice
					products {
						nodes {
							id
							name
							slug
						}
					}
					attributes {
						nodes {
							id
							name
							options
						}
					}
				}
			}
		}

		# Best Sellers (by total sales)
		BestSellers: products(first: 50, where: { orderby: { field: TOTAL_SALES, order: DESC } }) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				description
				shortDescription
				sku
				reviewCount
				averageRating
				onSale
				image {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}

				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
					stockQuantity
					totalSales
				}

				... on VariableProduct {
					price
					regularPrice
					salePrice
					stockStatus
					stockQuantity
					totalSales
				}

				... on ExternalProduct {
					price
					regularPrice
					salePrice
				}

				... on GroupProduct {
					price
					regularPrice
					salePrice
				}
			}
		}

		# Most Expensive (by price)
		MostExpensive: products(first: 50, where: { orderby: { field: PRICE, order: DESC } }) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				description
				shortDescription
				sku
				reviewCount
				averageRating
				onSale
				image {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}

				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on VariableProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on ExternalProduct {
					price
					regularPrice
					salePrice
				}

				... on GroupProduct {
					price
					regularPrice
					salePrice
				}
			}
		}

		# On Sale Products
		OnSale: products(first: 50, where: { onSale: true, orderby: { field: DATE, order: DESC } }) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				description
				shortDescription
				sku
				reviewCount
				averageRating
				onSale
				image {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}

				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on VariableProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on ExternalProduct {
					price
					regularPrice
					salePrice
				}

				... on GroupProduct {
					price
					regularPrice
					salePrice
				}
			}
		}

		# Best Rated Products
		BestRated: products(first: 50, where: { orderby: { field: RATING, order: DESC } }) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				description
				shortDescription
				sku
				reviewCount
				averageRating
				onSale
				image {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}

				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on VariableProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}

				... on ExternalProduct {
					price
					regularPrice
					salePrice
				}

				... on GroupProduct {
					price
					regularPrice
					salePrice
				}
			}
		}
	}
`;

export const SINGLE_PRODUCT_QUERY = gql`
	query SingleProductQuery($id: ID!) {
		product(id: $id, idType: SLUG) {
			id
			databaseId
			name
			slug
			uri
			description
			shortDescription
			sku
			date
			onSale
			productCategories {
				nodes {
					id
					name
					slug
				}
			}
			productTags {
				nodes {
					id
					name
					slug
				}
			}
			image {
				id
				sourceUrl
				altText
				mediaDetails {
					width
					height
				}
			}
			galleryImages {
				nodes {
					id
					sourceUrl
					altText
					mediaDetails {
						width
						height
					}
				}
			}

			... on SimpleProduct {
				price
				regularPrice
				salePrice
				stockStatus
				stockQuantity
				weight
				length
				width
				height
			}

			... on VariableProduct {
				price
				regularPrice
				salePrice
				stockStatus
				stockQuantity
				weight
				length
				width
				height
				variations {
					nodes {
						id
						name
						price
						regularPrice
						salePrice
						stockStatus
						stockQuantity
					}
				}
			}

			... on ExternalProduct {
				price
				regularPrice
				salePrice
				externalUrl
				buttonText
			}

			... on GroupProduct {
				price
				regularPrice
				salePrice
				products {
					nodes {
						id
						name
						slug
					}
				}
			}
		}
	}
`;

export const RELATED_PRODUCTS_QUERY = gql`
	query RelatedProductsQuery($categoryIn: [String], $exclude: [Int]) {
		products(where: { categoryIn: $categoryIn, exclude: $exclude, status: "publish" }, first: 8) {
			nodes {
				id
				databaseId
				name
				slug
				uri
				onSale
				type
				image {
					id
					sourceUrl
					altText
				}
				... on SimpleProduct {
					price
					regularPrice
					salePrice
					stockStatus
				}
				... on VariableProduct {
					price
					regularPrice
				}
				... on ExternalProduct {
					externalUrl
				}
			}
		}
	}
`;
