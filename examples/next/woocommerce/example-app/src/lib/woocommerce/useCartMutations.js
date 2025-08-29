import { useEffect, useState } from 'react';
import { useMutation } from '@apollo/client';

import { useSession } from './sessionProvider.js';
import {
  AddToCart,
  UpdateCartItemQuantities,
  RemoveItemsFromCart,
} from './graphQL.js';

const useCartMutations = (
  productId,
  variationId,
  extraData,
) => {
  const {
    cart,
    setCart,
    findInCart,
  } = useSession();

  // Fallback findInCart function if not available from session
  const safeFindInCart = (prodId, varId, extra) => {
    if (typeof findInCart === 'function') {
      return findInCart(prodId, varId, extra);
    }
    
    // Fallback implementation
    if (!cart?.contents?.nodes) {
      return null;
    }
    
    return cart.contents.nodes.find(item => {
      const matchesProduct = item.product?.databaseId === prodId || item.product?.id === prodId;
      const matchesVariation = varId ? item.variation?.databaseId === varId : !item.variation;
      return matchesProduct && matchesVariation;
    });
  };

  const [quantityFound, setQuantityInCart] = useState(
    safeFindInCart(productId, variationId, extraData)?.quantity || 0,
  );

  const [addToCart, { loading: adding }] = useMutation(AddToCart, {
    onCompleted({ addToCart: data }) {
      console.log('Add to cart completed:', data);
      if (data?.cart) {
        setCart(data.cart);
      }
    },
    onError(error) {
      console.error('Add to cart error:', error);
    }
  });

  const [updateQuantity, { loading: updating }] = useMutation(UpdateCartItemQuantities, {
    onCompleted({ updateItemQuantities: data }) {
      console.log('Update quantity completed:', data);
      if (data?.cart) {
        setCart(data.cart);
      }
    },
    onError(error) {
      console.error('Update quantity error:', error);
    }
  });

  const [removeCartItem, { loading: removing }] = useMutation(RemoveItemsFromCart, {
    onCompleted({ removeItemsFromCart: data }) {
      console.log('Remove item completed:', data);
      if (data?.cart) {
        setCart(data.cart);
      }
    },
    onError(error) {
      console.error('Remove item error:', error);
    }
  });

  useEffect(() => {
    const foundItem = safeFindInCart(productId, variationId, extraData);
    setQuantityInCart(foundItem?.quantity || 0);
  }, [productId, variationId, extraData, cart?.contents?.nodes]);

  const mutate = async (values) => {
    const {
      quantity,
      all = false,
      mutation = 'update',
    } = values;

    console.log('Mutate called with:', { values, productId, variationId, extraData });

    if (!cart) {
      console.error('No cart available');
      return { success: false, error: 'No cart available' };
    }

    if (!productId) {
      const error = 'No item provided.';
      console.error(error);
      throw new Error(error);
    }

    try {
      switch (mutation) {
        case 'remove': {
          if (!quantityFound) {
            throw new Error('Provided item not in cart');
          }

          const item = safeFindInCart(productId, variationId, extraData);

          if (!item) {
            throw new Error('Failed to find item in cart.');
          }

          const { key } = item;
          console.log('Removing item with key:', key);
          const result = await removeCartItem({ variables: { keys: [key], all } });
          return { success: true, data: result };
        }
        case 'update':
        default:
          if (quantityFound) {
            const item = safeFindInCart(productId, variationId, extraData);

            if (!item) {
              throw new Error('Failed to find item in cart.');
            }

            const { key } = item;
            console.log('Updating item with key:', key, 'quantity:', quantity);
            const result = await updateQuantity({ variables: { items: [{ key, quantity }] } });
            return { success: true, data: result };
          } else {
            console.log('Adding new item to cart:', { productId, variationId, quantity, extraData });
            const result = await addToCart({
              variables: {
                input: {
                  productId,
                  variationId,
                  quantity,
                  extraData,
                },
              },
            });
            return { success: true, data: result };
          }
      }
    } catch (error) {
      console.error('Mutation error:', error);
      return { success: false, error: error.message || error };
    }
  };

  return {
    quantityInCart: quantityFound,
    mutate,
    loading: adding || updating || removing,
  };
};

export default useCartMutations;