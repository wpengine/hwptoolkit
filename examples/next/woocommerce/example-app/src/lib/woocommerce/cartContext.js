"use client";
import { createContext, useContext, useState, useEffect, useMemo, useCallback } from "react";
import { useMutation, useLazyQuery } from "@apollo/client";
import { AddToCart, UpdateCartItemQuantities, GetCart } from "@/lib/woocommerce/graphQL";

const CartContext = createContext();

export function CartProvider({ children }) {
    const [cartData, setCartData] = useState(null);
    const [isInitialized, setIsInitialized] = useState(false);
    const [customerId, setCustomerId] = useState(null);
    const [customerIdLoaded, setCustomerIdLoaded] = useState(false);

    // Try to import useAuth, but handle if it's not available
    let useAuth;
    try {
        useAuth = require("@/lib/auth/AuthProvider").useAuth;
    } catch (error) {
        console.log("Auth provider not available");
        useAuth = () => ({ user: null, customer: null, customerId: null });
    }

    const auth = useAuth();

    // Get customer ID from auth context or localStorage
    useEffect(() => {
        console.log("🔄 Checking for customer ID...");
        
        // Priority 1: Auth context (if user is logged in)
        if (auth?.customerId) {
            console.log("👤 Found customer ID from auth context:", auth.customerId);
            setCustomerId(auth.customerId);
            setCustomerIdLoaded(true);
            return;
        }

        // Priority 2: localStorage (fallback)
        if (typeof window !== "undefined") {
            const savedCustomerId =
                localStorage.getItem("customer_id") || 
                localStorage.getItem("user_id") || 
                sessionStorage.getItem("customer_id");

            if (savedCustomerId) {
                console.log("👤 Found customer ID from localStorage:", savedCustomerId);
                setCustomerId(savedCustomerId);
            } else {
                console.log("👤 No customer ID found - guest user");
                setCustomerId(null);
            }
        } else {
            setCustomerId(null);
        }
        setCustomerIdLoaded(true);
    }, [auth?.customerId, auth?.user, auth?.customer]);

    // Get cart lazy query
    const [getCartQuery, { loading: getCartLoading, error: getCartError }] = useLazyQuery(GetCart, {
        onCompleted: (data) => {
            console.log("✅ Server cart fetched:", data?.cart);
        },
        onError: (error) => {
            console.error("❌ Get cart error:", error);
        },
        fetchPolicy: "network-only",
    });

    // Add to cart mutation
    const [addToCartMutation, { loading: addToCartLoading, error: addToCartError }] = useMutation(AddToCart, {
        onCompleted: (data) => {
            console.log("✅ Add to cart completed:", data);
            if (data?.addToCart?.cart) {
                const newCartData = data.addToCart.cart;
                console.log("🔄 Updating both localStorage and state with new cart data");
                
                setCartData(newCartData);
                saveCartToLocalStorage(newCartData);
            }
        },
        onError: (error) => {
            console.error("❌ Add to cart mutation error:", error);
        },
    });

    // Update quantity mutation
    const [updateQuantityMutation, { loading: updateQuantityLoading }] = useMutation(UpdateCartItemQuantities, {
        onCompleted: (data) => {
            console.log("✅ Update quantity completed:", data);
            if (data?.updateItemQuantities?.cart) {
                const newCartData = data.updateItemQuantities.cart;
                console.log("🔄 Updating both localStorage and state with updated cart");
                
                setCartData(newCartData);
                saveCartToLocalStorage(newCartData);
            }
        },
        onError: (error) => {
            console.error("❌ Update quantity mutation error:", error);
        },
    });

    // Helper function to save cart to localStorage
    const saveCartToLocalStorage = useCallback((cart) => {
        if (typeof window !== "undefined" && cart) {
            console.log("💾 Saving cart to localStorage:", cart);
            localStorage.setItem("wocommerce_cart_items", JSON.stringify(cart));
        }
    }, []);

    // Helper function to load cart from localStorage
    const loadCartFromLocalStorage = useCallback(() => {
        if (typeof window !== "undefined") {
            const savedCartData = localStorage.getItem("wocommerce_cart_items");
            if (savedCartData) {
                try {
                    const localCart = JSON.parse(savedCartData);
                    console.log("📱 Loaded cart from localStorage:", localCart);
                    return localCart;
                } catch (error) {
                    console.error("❌ Error parsing localStorage cart:", error);
                    localStorage.removeItem("wocommerce_cart_items");
                }
            }
        }
        return null;
    }, []);

    // Function to fetch cart from server (only for authenticated users)
    const fetchCart = useCallback(async () => {
        if (!customerId) {
            console.log("👤 No customer ID - skipping server cart fetch");
            return null;
        }

        try {
            console.log("🌐 Fetching cart from server...", { customerId });

            const variables = {
                customerId: customerId
            };

            const result = await getCartQuery({
                variables,
            });

            const serverCart = result.data?.cart || null;
            console.log("🌐 Server cart fetched:", serverCart);
            return serverCart;
        } catch (error) {
            console.error("❌ Error fetching cart:", error);
            return null;
        }
    }, [customerId, getCartQuery]);

    // Function to sync localStorage cart to server (called on login)
    const syncCartOnLogin = useCallback(
        async (newCustomerId) => {
            console.log("🔐🛒 Syncing cart on login with customer ID:", newCustomerId);
            
            // Update customer ID immediately
            setCustomerId(newCustomerId);
            
            // Save customer ID to localStorage
            if (typeof window !== "undefined") {
                localStorage.setItem("customer_id", newCustomerId.toString());
            }

            // Load cart from localStorage
            const localCart = loadCartFromLocalStorage();
            
            if (!localCart?.contents?.nodes?.length) {
                console.log("📭 No local cart items to sync on login");
                
                // Still fetch server cart to see if user has existing cart
                const serverCart = await fetchCart();
                if (serverCart) {
                    console.log("🌐 Setting server cart data");
                    setCartData(serverCart);
                    saveCartToLocalStorage(serverCart);
                }
                return;
            }

            console.log(`🔄 Syncing ${localCart.contents.nodes.length} items to server for logged in user...`);

            // Sync each item to server
            for (const item of localCart.contents.nodes) {
                try {
                    const productId = item.product?.databaseId || item.product?.node?.databaseId;
                    const variationId = item.variation?.databaseId || item.variation?.node?.databaseId;
                    const quantity = item.quantity;

                    if (productId && quantity > 0) {
                        console.log(`➕ Adding ${quantity}x product ${productId} to server cart for customer ${newCustomerId}`);

                        const variables = {
                            productId: parseInt(productId),
                            quantity: parseInt(quantity),
                            ...(variationId && { variationId: parseInt(variationId) }),
                            customerId: newCustomerId,
                        };

                        await addToCartMutation({
                            variables,
                        });
                    }
                } catch (error) {
                    console.error("❌ Error syncing item to server on login:", item, error);
                }
            }

            // After syncing, fetch fresh cart from server
            console.log("🔄 Fetching updated cart from server after login sync...");
            const updatedCart = await fetchCart();
            
            if (updatedCart) {
                console.log("✅ Login cart sync completed successfully");
                setCartData(updatedCart);
                saveCartToLocalStorage(updatedCart);
            }
        },
        [loadCartFromLocalStorage, addToCartMutation, fetchCart, saveCartToLocalStorage]
    );

    // Function to handle logout
    const handleLogout = useCallback(() => {
        console.log("🔐🛒 Handling cart logout");
        
        // Clear customer ID
        setCustomerId(null);
        
        // Remove customer ID from localStorage
        if (typeof window !== "undefined") {
            localStorage.removeItem("customer_id");
        }

        // Keep cart data in localStorage but clear customer-specific state
        console.log("📱 Keeping cart in localStorage for guest user");
        
        // Re-initialize as guest user
        setIsInitialized(false);
    }, []);

    // Function to sync localStorage cart to server (for regular sync)
    const syncLocalCartToServer = useCallback(
        async (localCart) => {
            if (!customerId) {
                console.log("👤 No customer ID - skipping server sync");
                return localCart;
            }

            console.log("🔄 Syncing localStorage cart to server...");
            
            if (!localCart?.contents?.nodes?.length) {
                console.log("📭 No local cart items to sync");
                return null;
            }

            const items = localCart.contents.nodes;
            console.log(`🔄 Syncing ${items.length} items to server...`);

            for (const item of items) {
                try {
                    const productId = item.product?.databaseId || item.product?.node?.databaseId;
                    const variationId = item.variation?.databaseId || item.variation?.node?.databaseId;
                    const quantity = item.quantity;

                    if (productId && quantity > 0) {
                        console.log(`➕ Adding ${quantity}x product ${productId} to server cart`);

                        const variables = {
                            productId: parseInt(productId),
                            quantity: parseInt(quantity),
                            ...(variationId && { variationId: parseInt(variationId) }),
                            customerId: customerId,
                        };

                        await addToCartMutation({
                            variables,
                        });
                    }
                } catch (error) {
                    console.error("❌ Error syncing item to server:", item, error);
                }
            }

            console.log("🔄 Fetching updated cart from server after sync...");
            const updatedCart = await fetchCart();
            
            if (updatedCart) {
                console.log("✅ Cart synced successfully");
                setCartData(updatedCart);
                saveCartToLocalStorage(updatedCart);
                return updatedCart;
            }
            
            return localCart;
        },
        [customerId, addToCartMutation, fetchCart, saveCartToLocalStorage]
    );

    // Initialize cart with different strategies for guest vs authenticated users
    useEffect(() => {
        const initializeCart = async () => {
            if (isInitialized || !customerIdLoaded) return;

            console.log("🚀 === CART INITIALIZATION START ===");
            console.log("👤 Customer ID:", customerId);

            const localCart = loadCartFromLocalStorage();
            const localItemCount = localCart?.contents?.nodes?.length || 0;
            console.log(`📱 localStorage has ${localItemCount} items`);

            if (!customerId) {
                console.log("👤 Guest user - using localStorage only");
                if (localCart) {
                    console.log("📱 Setting cart from localStorage");
                    setCartData(localCart);
                } else {
                    console.log("🆕 Starting with empty cart");
                    setCartData(null);
                }
            } else {
                console.log("👤 Authenticated user - syncing localStorage with server");

                const serverCart = await fetchCart();
                const serverItemCount = serverCart?.contents?.nodes?.length || 0;
                console.log(`🌐 Server has ${serverItemCount} items`);

                if (localItemCount > 0 && serverItemCount === 0) {
                    console.log("📱➡️🌐 localStorage has items but server is empty - syncing to server");
                    await syncLocalCartToServer(localCart);
                } else if (serverItemCount > 0 && localItemCount === 0) {
                    console.log("🌐➡️📱 Server has items but localStorage is empty - updating localStorage");
                    setCartData(serverCart);
                    saveCartToLocalStorage(serverCart);
                } else if (serverItemCount > 0 && localItemCount > 0) {
                    console.log("🌐📱 Both have items - using server as source of truth");
                    setCartData(serverCart);
                    saveCartToLocalStorage(serverCart);
                } else if (serverItemCount > 0) {
                    console.log("🌐 Using server cart");
                    setCartData(serverCart);
                    saveCartToLocalStorage(serverCart);
                } else if (localItemCount > 0) {
                    console.log("📱 Using localStorage cart and syncing to server");
                    await syncLocalCartToServer(localCart);
                } else {
                    console.log("🆕 Both empty - starting fresh");
                    setCartData(null);
                }
            }

            console.log("✅ === CART INITIALIZATION COMPLETE ===");
            setIsInitialized(true);
        };

        initializeCart();
    }, [customerIdLoaded, customerId, isInitialized, loadCartFromLocalStorage, fetchCart, syncLocalCartToServer, saveCartToLocalStorage]);

    // Memoized values
    const cartItemCount = useMemo(() => {
        const count = cartData?.contents?.nodes?.reduce((total, item) => total + (item.quantity || 0), 0) || 0;
        return count;
    }, [cartData]);

    const cartItems = useMemo(() => {
        return cartData?.contents?.nodes || [];
    }, [cartData]);

    const cartTotal = useMemo(() => {
        return cartData?.total || "0";
    }, [cartData]);

    // Helper function to find item in cart
    const findCartItem = useCallback(
        (productId, variationId = null) => {
            if (!cartData?.contents?.nodes) return null;

            return cartData.contents.nodes.find((item) => {
                const matchesProduct =
                    item.product?.node?.databaseId === parseInt(productId) ||
                    item.product?.node?.id === productId ||
                    item.product?.databaseId === parseInt(productId) ||
                    item.product?.id === productId;

                const matchesVariation = variationId
                    ? item.variation?.node?.databaseId === parseInt(variationId) ||
                      item.variation?.node?.id === variationId ||
                      item.variation?.databaseId === parseInt(variationId) ||
                      item.variation?.id === variationId
                    : !item.variation?.node && !item.variation;

                return matchesProduct && matchesVariation;
            });
        },
        [cartData]
    );

    // Add to cart function - handles both guest and authenticated users
    const addToCart = useCallback(
        async (productId, quantity = 1, variationId = null) => {
            try {
                console.log("🛒 Adding to cart:", { productId, quantity, variationId, customerId });

                const variables = {
                    productId: parseInt(productId),
                    quantity: parseInt(quantity),
                    ...(variationId && { variationId: parseInt(variationId) }),
                };

                if (customerId) {
                    variables.customerId = customerId;
                }

                const { data, errors } = await addToCartMutation({
                    variables,
                });

                if (errors && errors.length > 0) {
                    console.error("❌ Add mutation errors:", errors);
                    throw new Error(errors[0]?.message || "Failed to add to cart");
                }

                if (data?.addToCart?.cart) {
                    console.log("✅ Item added successfully");
                    return {
                        success: true,
                        cart: data.addToCart.cart,
                        action: "added",
                    };
                } else {
                    throw new Error("No cart data returned");
                }
            } catch (error) {
                console.error("❌ Add to cart error:", error);
                return {
                    success: false,
                    error: error.message || "Failed to add to cart",
                };
            }
        },
        [customerId, addToCartMutation]
    );

    // Other functions remain the same...
    const refreshCart = useCallback(async () => {
        try {
            console.log("🔄 Refreshing cart...");
            
            if (!customerId) {
                console.log("👤 Guest user - refreshing from localStorage");
                const localCart = loadCartFromLocalStorage();
                if (localCart) {
                    setCartData(localCart);
                }
                return localCart;
            } else {
                console.log("👤 Authenticated user - refreshing from server");
                const updatedCart = await fetchCart();
                if (updatedCart) {
                    console.log("✅ Cart refreshed, updating both state and localStorage");
                    setCartData(updatedCart);
                    saveCartToLocalStorage(updatedCart);
                }
                return updatedCart;
            }
        } catch (error) {
            console.error("❌ Error refreshing cart:", error);
            return null;
        }
    }, [customerId, fetchCart, saveCartToLocalStorage, loadCartFromLocalStorage]);

    const clearCart = useCallback(async () => {
        try {
            console.log("🗑️ Clearing cart...");

            if (typeof window !== "undefined") {
                localStorage.removeItem("wocommerce_cart_items");
            }

            setCartData(null);

            console.log("✅ Cart cleared successfully");
        } catch (error) {
            console.error("❌ Error clearing cart:", error);
        }
    }, []);

    const updateCustomerId = useCallback((newCustomerId) => {
        console.log("👤 Updating customer ID:", newCustomerId);
        
        const previousCustomerId = customerId;
        setCustomerId(newCustomerId);

        if (typeof window !== "undefined") {
            if (newCustomerId) {
                localStorage.setItem("customer_id", newCustomerId);
            } else {
                localStorage.removeItem("customer_id");
            }
        }

        if (!previousCustomerId && newCustomerId) {
            console.log("👤 User logged in - will sync localStorage to server");
            setIsInitialized(false);
        } else if (previousCustomerId && !newCustomerId) {
            console.log("👤 User logged out - keeping localStorage cart");
            setIsInitialized(false);
        } else if (previousCustomerId !== newCustomerId) {
            console.log("👤 User switched - re-initializing cart");
            setIsInitialized(false);
        }
    }, [customerId]);

    // Memoized context value
    const value = useMemo(
        () => ({
            // Cart data
            cart: cartData,
            loading: getCartLoading || addToCartLoading || updateQuantityLoading || !isInitialized || !customerIdLoaded,
            error: getCartError || addToCartError,
            isInitialized,
            customerId,
            customerIdLoaded,
            isGuest: !customerId,
            isAuthenticated: !!customerId,

            // Functions that return values (memoized)
            getCartItemCount: () => cartItemCount,
            getCartItems: () => cartItems,
            getCartTotal: () => cartTotal,

            // Action functions
            addToCart,
            findCartItem,
            clearCart,
            refreshCart,
            updateCustomerId,
            fetchCart,

            // Sync functions for auth integration
            syncCartOnLogin,
            handleLogout,
            syncLocalCartToServer,
            loadCartFromLocalStorage,
            saveCartToLocalStorage,
        }),
        [
            cartData,
            getCartLoading,
            addToCartLoading,
            updateQuantityLoading,
            isInitialized,
            customerIdLoaded,
            getCartError,
            addToCartError,
            customerId,
            cartItemCount,
            cartItems,
            cartTotal,
            addToCart,
            findCartItem,
            clearCart,
            refreshCart,
            updateCustomerId,
            fetchCart,
            syncCartOnLogin,
            handleLogout,
            syncLocalCartToServer,
            loadCartFromLocalStorage,
            saveCartToLocalStorage,
        ]
    );

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export const useCart = () => {
    const context = useContext(CartContext);
    if (!context) {
        throw new Error("useCart must be used within a CartProvider");
    }
    return context;
};