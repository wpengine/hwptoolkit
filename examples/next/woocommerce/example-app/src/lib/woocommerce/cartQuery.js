import { useQuery, gql } from '@apollo/client';

const GET_CART = gql`
  query GetCart {
    cart {
      contents {
        nodes {
          key
          product {
            node {
              id
              productId: databaseId
              name
              slug
              link
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
                stockQuantity
              }
              ... on VariableProduct {
                price
                regularPrice
                salePrice
                stockStatus
                stockQuantity
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
          variation {
            node {
              id
              variationId: databaseId
              name
              description
              price
              regularPrice
              salePrice
              image {
                id
                sourceUrl
                altText
              }
            }
          }
          quantity
          total
          subtotal
          subtotalTax
        }
      }
      appliedCoupons {
        code
        discountAmount
        discountTax
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
      contentsTotal
      contentsTax
      isEmpty
      chosenShippingMethods
      availableShippingMethods {
        packageDetails
        supportsShippingCalculator
        rates {
          id
          instanceId
          methodId
          cost
          label
        }
      }
    }
  }
`;

export function useCartQuery() {
  return useQuery(GET_CART, {
    errorPolicy: 'all',
    notifyOnNetworkStatusChange: true,
  });
}