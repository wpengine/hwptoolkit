import React, { createContext, useContext, useEffect, useState, useMemo, useCallback } from "react";
import { useMutation, useLazyQuery } from "@apollo/client";
import { useAuth } from "../auth/AuthProvider";
import useLocalStorage from "../storage";
import { AddToCart, GetMiniCart, UpdateCartItemQuantities } from "@/lib/woocommerce/graphQL";

const CartContext = createContext(undefined);

export function CartProvider({ children }) {
    const storage = useLocalStorage;
    const { userId, isAuthenticated } = useAuth(); // Subscribe to auth changes

    const [cartData, setCartData] = useState(null);
    const [isCartInitialized, setIsCartInitialized] = useState(false);

    // Cart Mutations
    const [addToCartMutation, { loading: addToCartLoading }] = useMutation(AddToCart, {
        onCompleted: (data) => {
            if (data?.addToCart?.cart) {
                setCartData(data.addToCart.cart);
                saveCartToLocalStorage(data.addToCart.cart);
            }
        },
        onError: (error) => console.error("❌ Add to cart error:", error),
    });

    const [updateCartMutation, { loading: updateCartLoading }] = useMutation(UpdateCartItemQuantities, {
        onCompleted: (data) => {
            if (data?.updateItemQuantities?.cart) {
                setCartData(data.updateItemQuantities.cart);
                saveCartToLocalStorage(data.updateItemQuantities.cart);
            }
        },
        onError: (error) => console.error("❌ Update cart error:", error),
    });

    const [getCartQuery, { loading: getCartLoading }] = useLazyQuery(GetMiniCart, {
        onCompleted: (data) => {
            if (data?.cart) {
                setCartData(data.cart);
                saveCartToLocalStorage(data.cart);
            }
        },
        onError: (error) => console.error("❌ GetCart error:", error),
        fetchPolicy: "network-only",
        errorPolicy: "all",
    });

    // Helper functions
    const saveCartToLocalStorage = useCallback(
        (cart) => {
            if (cart) {
                storage.setItem("woocommerce_cart", JSON.stringify(cart));
            }
        },
        [storage]
    );

    const loadCartFromLocalStorage = useCallback(() => {
        const data = storage.getItem("woocommerce_cart");
        if (data) {
            try {
                return JSON.parse(data);
            } catch (error) {
                console.error("Error parsing cart:", error);
                storage.removeItem("woocommerce_cart");
            }
        }
        return null;
    }, [storage]);

    // Initialize Cart when auth changes
    useEffect(() => {
        let isMounted = true;

        const initializeCart = async () => {
            console.log("🛒 Initializing cart...", { userId, isCartInitialized });

            const localCart = loadCartFromLocalStorage();

            if (!userId) {
                // Guest user
                if (isMounted) {
                    setCartData(localCart || null);
                    setIsCartInitialized(true);
                }
            } else {
                // Authenticated user
                try {
                    const result = await getCartQuery();
                    if (isMounted) {
                        setCartData(result.data?.cart || localCart || null);
                        setIsCartInitialized(true);
                    }
                } catch (error) {
                    console.error("❌ Cart error:", error);
                    if (isMounted) {
                        setCartData(localCart || null);
                        setIsCartInitialized(true);
                    }
                }
            }
        };

        // Reset and reinitialize cart when userId changes
        if (!isCartInitialized || cartData === null) {
            initializeCart();
        }

        return () => {
            isMounted = false;
        };
    }, [userId]); // Reinitialize when user logs in/out

    // Cart functions
    const findCartItem = useCallback(
        (productId, variationId = null) => {
            if (!cartData?.contents?.nodes) return null;

            return cartData.contents.nodes.find((item) => {
                const matchesProduct =
                    item.product?.node?.databaseId === parseInt(productId) ||
                    item.product?.databaseId === parseInt(productId);

                const matchesVariation = variationId
                    ? item.variation?.node?.databaseId === parseInt(variationId) ||
                      item.variation?.databaseId === parseInt(variationId)
                    : !item.variation?.node && !item.variation;

                return matchesProduct && matchesVariation;
            });
        },
        [cartData]
    );

    const addToCart = useCallback(
        async (productId, quantity = 1, variationId = null) => {
            try {
                const existingItem = findCartItem(productId, variationId);

                if (existingItem) {
                    const newQuantity = existingItem.quantity + quantity;
                    const { data, errors } = await updateCartMutation({
                        variables: {
                            items: [{ key: existingItem.key, quantity: newQuantity }],
                        },
                    });

                    if (errors?.length > 0) {
                        throw new Error(errors[0]?.message || "Failed to update cart");
                    }

                    return { success: true, cart: data?.updateItemQuantities?.cart, action: "updated" };
                } else {
                    const variables = {
                        productId: parseInt(productId),
                        quantity: parseInt(quantity),
                    };

                    if (variationId) {
                        variables.variationId = parseInt(variationId);
                    }

                    const { data, errors } = await addToCartMutation({ variables });

                    if (errors?.length > 0) {
                        throw new Error(errors[0]?.message || "Failed to add to cart");
                    }

                    return { success: true, cart: data?.addToCart?.cart, action: "added" };
                }
            } catch (error) {
                console.error("❌ Add to cart error:", error);
                return { success: false, error: error.message || "Failed to add to cart" };
            }
        },
        [addToCartMutation, updateCartMutation, findCartItem]
    );

    const updateCartItemQuantity = useCallback(
        async (itemKey, newQuantity) => {
            try {
                if (newQuantity <= 0) {
                    return { success: false, error: "Quantity must be greater than 0" };
                }

                const { data, errors } = await updateCartMutation({
                    variables: {
                        items: [{ key: itemKey, quantity: parseInt(newQuantity) }],
                    },
                });

                if (errors?.length > 0) {
                    throw new Error(errors[0]?.message || "Failed to update cart");
                }

                return { success: true, cart: data?.updateItemQuantities?.cart, action: "updated" };
            } catch (error) {
                console.error("❌ Update cart quantity error:", error);
                return { success: false, error: error.message || "Failed to update cart quantity" };
            }
        },
        [updateCartMutation]
    );

    const clearCart = useCallback(() => {
        storage.removeItem("woocommerce_cart");
        setCartData(null);
    }, [storage]);

    const refreshCart = useCallback(async () => {
        try {
            const result = await getCartQuery();
            return result.data?.cart || null;
        } catch (error) {
            console.error("❌ Error refreshing cart:", error);
            const localCart = loadCartFromLocalStorage();
            setCartData(localCart);
            return localCart;
        }
    }, [getCartQuery, loadCartFromLocalStorage]);

    // Computed values
    const cartItemCount = useMemo(() => {
        return cartData?.contents?.itemCount || 0;
    }, [cartData]);

    const cartItems = useMemo(() => {
        return cartData?.contents?.nodes || [];
    }, [cartData]);

    const cartTotal = useMemo(() => {
        return cartData?.total || "0";
    }, [cartData]);

    const value = {
        // State
        cart: cartData,
        cartItemCount,
        cartItems,
        cartTotal,
        isCartInitialized,
        cartLoading: getCartLoading || addToCartLoading || updateCartLoading,

        // Functions
        addToCart,
        updateCartItemQuantity,
        clearCart,
        refreshCart,
        findCartItem,
    };

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export const useCart = () => {
    const context = useContext(CartContext);
    if (context === undefined) {
        throw new Error("useCart must be used within a CartProvider");
    }
    return context;
};