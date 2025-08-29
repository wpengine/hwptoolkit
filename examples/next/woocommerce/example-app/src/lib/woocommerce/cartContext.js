'use client';
import { createContext, useContext } from 'react';
import { useCartMutations } from '@/lib/woocommerce/cart';
import { useCartQuery } from '@/lib/woocommerce/cartQuery';

const CartContext = createContext();

export function CartProvider({ children }) {
  const cartQuery = useCartQuery();
  const cartMutations = useCartMutations();

  const addToCart = async (productId, quantity = 1, variationId = null) => {
    try {
      const { data, errors } = await cartMutations.addToCart({
        variables: {
          input: {
            productId: parseInt(productId),
            quantity,
            ...(variationId && { variationId: parseInt(variationId) }),
          },
        },
      });
      if (errors) {
        throw new Error(errors[0]?.message || 'Failed to add to cart');
      }

      if (data.addToCart.cart.contents.nodes) {
        // Refetch cart to update UI
        console.log('cart refetch');
        await cartQuery.refetch();
        console.log('cart refetch done', { cart });
        return { success: true, cartItem: data.addToCart.cart.contents.nodes };
      } else {
        throw new Error('No cart item returned');
      }
    } catch (error) {
      console.error('Add to cart error:', error);
      return { success: false, error: error.message };
    }
  };

  const cart = cartQuery.data?.cart;
  
  const getCartItemCount = () => {
    console.log(cart);
    return cart?.contents?.nodes?.reduce((total, item) => total + item.quantity, 0) || 0;
  };

  const value = {
    // Cart data
    cart,
    loading: cartQuery.loading || cartMutations.loading,
    error: cartQuery.error,
    
    // Simple functions
    addToCart,
    getCartItemCount,
    
    // Full access to queries and mutations
    cartQuery,
    cartMutations,
  };

  return (
    <CartContext.Provider value={value}>
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
};