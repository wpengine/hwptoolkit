import { useMutation, gql } from '@apollo/client';

const ADD_TO_CART = gql`
  mutation AddToCart($input: AddToCartInput!) {
    addToCart(input: $input) {
      cartItem {
        key
        product {
          node {
            id
            productId: databaseId
            name
            description
            type
            onSale
            slug
            averageRating
            reviewCount
            image {
              id
              sourceUrl
              srcSet
              altText
              title
            }
            galleryImages {
              nodes {
                id
                sourceUrl
                srcSet
                altText
                title
              }
            }
          }
        }
        variation {
          node {
            id
            variationId: databaseId
            name
            description
            type
            onSale
            price
            regularPrice
            salePrice
            image {
              id
              sourceUrl
              srcSet
              altText
              title
            }
          }
        }
        quantity
        total
        subtotal
        subtotalTax
      }
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
                image {
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
                  salePrice
                  stockStatus
                }
              }
            }
            variation {
              node {
                id
                name
                price
              }
            }
            quantity
            total
            subtotal
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
      }
    }
  }
`;


const UPDATE_ITEM_QUANTITIES = gql`
  mutation UpdateItemQuantities($input: UpdateItemQuantitiesInput!) {
    updateItemQuantities(input: $input) {
      updated {
        key
        quantity
        total
        subtotal
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

const REMOVE_ITEMS_FROM_CART = gql`
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

const APPLY_COUPON = gql`
  mutation ApplyCoupon($input: ApplyCouponInput!) {
    applyCoupon(input: $input) {
      applied {
        code
        discountAmount
        discountTax
      }
      cart {
        appliedCoupons {
          code
          discountAmount
          discountTax
        }
        subtotal
        total
        totalTax
        discountTotal
        discountTax
      }
    }
  }
`;


const REMOVE_COUPONS = gql`
  mutation RemoveCoupons($input: RemoveCouponsInput!) {
    removeCoupons(input: $input) {
      cart {
        appliedCoupons {
          code
          discountAmount
          discountTax
        }
        subtotal
        total
        totalTax
        discountTotal
        discountTax
      }
    }
  }
`;

export function useCartMutations() {
  const [addToCart, { loading: addToCartLoading }] = useMutation(ADD_TO_CART);
  const [updateItemQuantities, { loading: updateCartLoading }] = useMutation(UPDATE_ITEM_QUANTITIES);
  const [removeItemsFromCart, { loading: removeCartLoading }] = useMutation(REMOVE_ITEMS_FROM_CART);

  return {
    addToCart,
    updateItemQuantities,
    removeItemsFromCart,
    loading: addToCartLoading || updateCartLoading || removeCartLoading,
  };
}

export function useOtherCartMutations() {
  const [applyCoupon, { loading: applyCouponLoading }] = useMutation(APPLY_COUPON);
  const [removeCoupons, { loading: removeCouponsLoading }] = useMutation(REMOVE_COUPONS);

  return {
    applyCoupon,
    removeCoupons,
    loading: applyCouponLoading || removeCouponsLoading,
  };
}