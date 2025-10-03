"use client";
import React, { createContext, useContext, useEffect, useState, useMemo, useCallback } from "react";
import { useRouter } from "next/router";
import { gql, useMutation, useLazyQuery } from "@apollo/client";

// Import GraphQL operations from your existing file
import { AddToCart, GetCart, UpdateCartItemQuantities, LOGIN_MUTATION } from "@/lib/woocommerce/graphQL";

const REFRESH_TOKEN_MUTATION = gql`
    mutation refreshToken($token: String!) {
        refreshToken(input: { refreshToken: $token }) {
            authToken
            authTokenExpiration
            success
        }
    }
`;

const AppContext = createContext();

export function AppProvider({ children }) {
    const router = useRouter();

    // Auth State
    const [authState, setAuthState] = useState({
        user: null,
        customer: null,
        tokens: null,
        isLoading: true,
    });

    // Cart State
    const [cartData, setCartData] = useState(null);
    const [isCartInitialized, setIsCartInitialized] = useState(false);
    const [cartSessionToken, setCartSessionToken] = useState(null); // Add session token state

    // Get customer ID from auth state
    const customerId = authState.customer?.databaseId || authState.user?.databaseId || null;

    // Auth Mutations
    const [loginMutation] = useMutation(LOGIN_MUTATION);
    const [refreshTokenMutation] = useMutation(REFRESH_TOKEN_MUTATION);

    // Cart Mutations and Queries - with session token context
    const [addToCartMutation, { loading: addToCartLoading }] = useMutation(AddToCart, {
        context: {
            headers: cartSessionToken ? { 'woocommerce-session': `Session ${cartSessionToken}` } : {},
        },
        onCompleted: (data) => {
            console.log("✅ Add to cart completed:", data);
            if (data?.addToCart?.cart) {
                const newCartData = data.addToCart.cart;
                console.log("📦 New cart data from addToCart:", newCartData);
                setCartData(newCartData);
                saveCartToLocalStorage(newCartData);
                
                // Extract and save session token if present
                if (data.addToCart.cart.sessionToken) {
                    setCartSessionToken(data.addToCart.cart.sessionToken);
                    saveToLocalStorage("cart_session_token", data.addToCart.cart.sessionToken);
                }
            }
        },
        onError: (error) => {
            console.error("❌ Add to cart error:", error);
        },
    });

    const [updateCartMutation, { loading: updateCartLoading }] = useMutation(UpdateCartItemQuantities, {
        context: {
            headers: cartSessionToken ? { 'woocommerce-session': `Session ${cartSessionToken}` } : {},
        },
        onCompleted: (data) => {
            console.log("✅ Update cart completed:", data);
            if (data?.updateItemQuantities?.cart) {
                const newCartData = data.updateItemQuantities.cart;
                console.log("📦 New cart data from updateCart:", newCartData);
                setCartData(newCartData);
                saveCartToLocalStorage(newCartData);
            }
        },
        onError: (error) => {
            console.error("❌ Update cart error:", error);
        },
    });

    const [getCartQuery, { loading: getCartLoading }] = useLazyQuery(GetCart, {
        context: {
            headers: cartSessionToken ? { 'woocommerce-session': `Session ${cartSessionToken}` } : {},
        },
        onCompleted: (data) => {
            console.log("✅ Server cart fetched:", data?.cart);
            console.log("📦 Server cart contents:", data?.cart?.contents?.nodes);
            if (data?.cart) {
                // Only update state if we have valid product data OR if cart is empty
                const hasValidProducts = data.cart.contents?.nodes?.every(node => 
                    node.product?.node?.databaseId || node.product?.databaseId
                );
                
                if (hasValidProducts || data.cart.contents?.nodes?.length === 0) {
                    console.log("✅ Server cart has valid data, updating state");
                    setCartData(data.cart);
                    saveCartToLocalStorage(data.cart);
                    
                    // Save session token if present
                    if (data.cart.sessionToken) {
                        setCartSessionToken(data.cart.sessionToken);
                        saveToLocalStorage("cart_session_token", data.cart.sessionToken);
                    }
                } else {
                    console.log("⚠️ Server cart has invalid product data, keeping current cart");
                    console.log("Invalid cart data:", data.cart);
                }
            }
        },
        onError: (error) => {
            console.error("❌ GetCart error:", error);
        },
        fetchPolicy: "network-only",
        errorPolicy: "all",
    });

    // Helper functions for localStorage
    const saveToLocalStorage = useCallback((key, data) => {
        if (typeof window !== "undefined") {
            localStorage.setItem(key, JSON.stringify(data));
        }
    }, []);

    const getFromLocalStorage = useCallback((key) => {
        if (typeof window !== "undefined") {
            const data = localStorage.getItem(key);
            if (data) {
                try {
                    return JSON.parse(data);
                } catch (error) {
                    console.error(`Error parsing ${key} from localStorage:`, error);
                    localStorage.removeItem(key);
                }
            }
        }
        return null;
    }, []);

    const removeFromLocalStorage = useCallback((key) => {
        if (typeof window !== "undefined") {
            localStorage.removeItem(key);
        }
    }, []);

    // Cart helper functions
    const saveCartToLocalStorage = useCallback(
        (cart) => {
            if (cart) {
                console.log("💾 Saving cart to localStorage");
                saveToLocalStorage("woocommerce_cart_items", cart);
            }
        },
        [saveToLocalStorage]
    );

    const loadCartFromLocalStorage = useCallback(() => {
        const localCart = getFromLocalStorage("woocommerce_cart_items");
        if (localCart) {
            console.log("📱 Loaded cart from localStorage:", localCart);
            console.log("📦 LocalStorage cart contents:", localCart?.contents?.nodes);
        }
        return localCart;
    }, [getFromLocalStorage]);

    // Load cart session token on initialization
    useEffect(() => {
        const savedSessionToken = getFromLocalStorage("cart_session_token");
        if (savedSessionToken) {
            console.log("🔗 Loaded cart session token:", savedSessionToken);
            setCartSessionToken(savedSessionToken);
        }
    }, [getFromLocalStorage]);

    // Initialize Auth on app start
    useEffect(() => {
        const initializeAuth = () => {
            try {
                console.log("🔐 Initializing auth...");
                const storedTokens = getFromLocalStorage("authTokens");
                const storedUser = getFromLocalStorage("user");
                const storedCustomer = getFromLocalStorage("customer");

                if (storedTokens && storedUser) {
                    console.log("✅ Found stored auth data");
                    setAuthState({
                        tokens: storedTokens,
                        user: storedUser,
                        customer: storedCustomer,
                        isLoading: false,
                    });
                } else {
                    console.log("📭 No stored auth data");
                    setAuthState((prev) => ({ ...prev, isLoading: false }));
                }
            } catch (error) {
                console.error("❌ Error initializing auth:", error);
                setAuthState((prev) => ({ ...prev, isLoading: false }));
            }
        };

        initializeAuth();
    }, [getFromLocalStorage]);

    // Initialize Cart after Auth is loaded
    useEffect(() => {
        const initializeCart = async () => {
            if (authState.isLoading || isCartInitialized) return;

            console.log("🛒 === CART INITIALIZATION START ===");
            console.log("👤 Customer ID:", customerId);
            console.log("🔗 Cart Session Token:", cartSessionToken);

            const localCart = loadCartFromLocalStorage();
            const localItemCount = localCart?.contents?.nodes?.length || 0;
            console.log(`📱 localStorage has ${localItemCount} items`);

            if (!customerId) {
                // Guest user - use localStorage AND server cart with session token
                console.log("👤 Guest user - managing cart with session token");
                
                if (cartSessionToken) {
                    // We have a session token, fetch server cart
                    try {
                        console.log("🌐 Fetching server cart with session token...");
                        const result = await getCartQuery();
                        const serverCart = result.data?.cart || null;
                        
                        if (serverCart && serverCart.contents?.nodes?.length > 0) {
                            console.log("🌐 Using server cart for guest user");
                            setCartData(serverCart);
                            saveCartToLocalStorage(serverCart);
                        } else if (localCart && localItemCount > 0) {
                            console.log("📱 Server cart empty, but localStorage has items - need to sync");
                            setCartData(localCart);
                            // Don't sync to server for guest users without proper session management
                        } else {
                            console.log("🆕 Both empty - starting fresh");
                            setCartData(null);
                        }
                    } catch (error) {
                        console.error("❌ Error fetching server cart for guest:", error);
                        if (localCart) {
                            setCartData(localCart);
                        }
                    }
                } else {
                    // No session token, use localStorage only
                    console.log("📱 No session token, using localStorage only");
                    if (localCart) {
                        setCartData(localCart);
                    } else {
                        setCartData(null);
                    }
                }
            } else {
                // Authenticated user - sync with server
                console.log("👤 Authenticated user - syncing with server");

                try {
                    console.log("🌐 Fetching server cart for authenticated user...");
                    const result = await getCartQuery();

                    const serverCart = result.data?.cart || null;
                    const serverItemCount = serverCart?.contents?.nodes?.length || 0;
                    console.log(`🌐 Server has ${serverItemCount} items`);

                    if (localItemCount > 0 && serverItemCount === 0) {
                        console.log("📱➡️🌐 localStorage has items but server is empty - syncing to server");
                        await syncLocalCartToServer(localCart);
                    } else if (serverItemCount > 0 && localItemCount === 0) {
                        console.log("🌐➡️📱 Server has items but localStorage is empty - using server cart");
                        const hasValidProducts = serverCart.contents?.nodes?.every(node => 
                            node.product?.node?.databaseId || node.product?.databaseId
                        );
                        if (hasValidProducts) {
                            setCartData(serverCart);
                            saveCartToLocalStorage(serverCart);
                        } else {
                            console.log("⚠️ Server cart has invalid data, keeping localStorage");
                            if (localCart) setCartData(localCart);
                        }
                    } else if (serverItemCount > 0 && localItemCount > 0) {
                        console.log("🌐📱 Both have items - using server as source of truth");
                        const hasValidProducts = serverCart.contents?.nodes?.every(node => 
                            node.product?.node?.databaseId || node.product?.databaseId
                        );
                        if (hasValidProducts) {
                            setCartData(serverCart);
                            saveCartToLocalStorage(serverCart);
                        } else {
                            console.log("⚠️ Server cart invalid, syncing localStorage to server");
                            await syncLocalCartToServer(localCart);
                        }
                    } else if (localItemCount > 0) {
                        console.log("📱 Only localStorage has items - syncing to server");
                        await syncLocalCartToServer(localCart);
                    } else {
                        console.log("🆕 Both empty - starting fresh");
                        setCartData(null);
                    }
                } catch (error) {
                    console.error("❌ Error initializing cart:", error);
                    if (localCart) {
                        console.log("🔄 Fallback: using localStorage cart");
                        setCartData(localCart);
                    }
                }
            }

            console.log("✅ === CART INITIALIZATION COMPLETE ===");
            setIsCartInitialized(true);
        };

        initializeCart();
    }, [authState.isLoading, customerId, isCartInitialized, cartSessionToken, loadCartFromLocalStorage, getCartQuery]);

    // Sync localStorage cart to server (only for authenticated users)
    const syncLocalCartToServer = useCallback(
        async (localCart) => {
            if (!customerId || !localCart?.contents?.nodes?.length) {
                console.log("⚠️ Cannot sync: no customerId or no local cart items");
                return;
            }

            console.log(`🔄 Syncing ${localCart.contents.nodes.length} items to server...`);

            // Add each item from localStorage to server
            for (const item of localCart.contents.nodes) {
                try {
                    const productId = item.product?.node?.databaseId || item.product?.databaseId;
                    const variationId = item.variation?.node?.databaseId || item.variation?.databaseId;
                    const quantity = item.quantity;

                    if (productId && quantity > 0) {
                        console.log(`➕ Syncing ${quantity}x product ${productId} to server`);

                        const variables = {
                            productId: parseInt(productId),
                            quantity: parseInt(quantity),
                        };

                        if (variationId) {
                            variables.variationId = parseInt(variationId);
                        }

                        await addToCartMutation({ variables });
                        
                        // Add small delay between mutations to avoid overwhelming server
                        await new Promise(resolve => setTimeout(resolve, 100));
                    }
                } catch (error) {
                    console.error("❌ Error syncing item:", item, error);
                }
            }

            console.log("✅ Cart sync completed");
        },
        [customerId, addToCartMutation]
    );

    // Find existing cart item
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

    // Auth Functions (unchanged)
    const login = useCallback(
        async (username, password) => {
            try {
                console.log("🔐 Starting login...");

                const { data } = await loginMutation({
                    variables: { username, password },
                });

                const { user, customer, ...tokens } = data.login;
                console.log("✅ Login successful:", { user, customer });

                // Store auth data
                saveToLocalStorage("authTokens", tokens);
                saveToLocalStorage("user", user);
                if (customer) {
                    saveToLocalStorage("customer", customer);
                }

                // Update auth state
                setAuthState({
                    user,
                    customer,
                    tokens,
                    isLoading: false,
                });

                // Handle cart sync after login
                // const newCustomerId = customer?.databaseId || user?.databaseId;
                // console.log("🔐 Login completed, handling cart sync for customer:", newCustomerId);

                // if (newCustomerId) {
                //     const localCart = loadCartFromLocalStorage();
                //     console.log("📱 localStorage cart at login:", localCart);

                //     if (localCart?.contents?.nodes?.length > 0) {
                //         console.log("🛒 Found localStorage items, will sync after auth state update");
                        
                //         setTimeout(async () => {
                //             try {
                //                 console.log("🔄 Starting delayed cart sync after login...");
                //                 await syncLocalCartToServer(localCart);
                //             } catch (error) {
                //                 console.error("❌ Error in delayed cart sync:", error);
                //             }
                //         }, 500);
                //     } else {
                //         console.log("📭 No localStorage items, will fetch server cart");
                //     }
                    
                //     setIsCartInitialized(false);
                // }

                router.push("/my-account");
            } catch (error) {
                console.error("❌ Login error:", error);
                throw error;
            }
        },
        [loginMutation, saveToLocalStorage, router, loadCartFromLocalStorage, syncLocalCartToServer]
    );

    const logout = useCallback(() => {
        console.log("🔐 Logging out...");

        // Clear auth storage
        removeFromLocalStorage("authTokens");
        removeFromLocalStorage("user");
        removeFromLocalStorage("customer");

        // Update auth state
        setAuthState({
            user: null,
            customer: null,
            tokens: null,
            isLoading: false,
        });

        // Keep cart session for guest user
        console.log("🛒 Reinitializing cart as guest user");
        setIsCartInitialized(false);

        router.push("/");
    }, [removeFromLocalStorage, router]);

    const refreshAuth = useCallback(async () => {
        if (!authState.tokens?.refreshToken) return;

        try {
            const { data } = await refreshTokenMutation({
                variables: { token: authState.tokens.refreshToken },
            });

            const { authToken, authTokenExpiration, success } = data.refreshToken;

            if (!success) {
                throw new Error("Failed to refresh token");
            }

            const newTokens = {
                authToken,
                refreshToken: authState.tokens.refreshToken,
                authTokenExpiration,
                refreshTokenExpiration: authState.tokens.refreshTokenExpiration,
            };

            saveToLocalStorage("authTokens", newTokens);

            setAuthState((prev) => ({
                ...prev,
                tokens: newTokens,
            }));
        } catch (error) {
            console.error("❌ Token refresh error:", error);
            logout();
        }
    }, [authState.tokens, refreshTokenMutation, saveToLocalStorage, logout]);

    // Cart Functions - Fixed to handle quantity increments properly
    const addToCart = useCallback(
        async (productId, quantity = 1, variationId = null) => {
            try {
                console.log("🛒 Adding to cart:", { productId, quantity, variationId, customerId, cartSessionToken });

                // Check if item already exists in cart
                const existingItem = findCartItem(productId, variationId);

                if (existingItem) {
                    // Item exists, update quantity instead of adding
                    console.log("📈 Item exists, updating quantity...");

                    const newQuantity = existingItem.quantity + quantity;

                    const updateVariables = {
                        items: [
                            {
                                key: existingItem.key,
                                quantity: newQuantity,
                            },
                        ],
                    };

                    const { data, errors } = await updateCartMutation({
                        variables: updateVariables,
                    });

                    if (errors?.length > 0) {
                        throw new Error(errors[0]?.message || "Failed to update cart");
                    }

                    if (data?.updateItemQuantities?.cart) {
                        console.log("✅ Cart updated successfully");
                        return {
                            success: true,
                            cart: data.updateItemQuantities.cart,
                            action: "updated",
                        };
                    }
                } else {
                    // Add new item
                    const variables = {
                        productId: parseInt(productId),
                        quantity: parseInt(quantity),
                    };

                    if (variationId) {
                        variables.variationId = parseInt(variationId);
                    }

                    console.log("➕ Adding new item with variables:", variables);

                    const { data, errors } = await addToCartMutation({ variables });

                    if (errors?.length > 0) {
                        throw new Error(errors[0]?.message || "Failed to add to cart");
                    }

                    if (data?.addToCart?.cart) {
                        console.log("✅ Item added successfully");
                        return {
                            success: true,
                            cart: data.addToCart.cart,
                            action: "added",
                        };
                    }
                }

                throw new Error("No cart data returned");
            } catch (error) {
                console.error("❌ Add to cart error:", error);
                return {
                    success: false,
                    error: error.message || "Failed to add to cart",
                };
            }
        },
        [customerId, cartSessionToken, addToCartMutation, updateCartMutation, findCartItem]
    );

    // Update cart item quantity
    const updateCartItemQuantity = useCallback(
        async (itemKey, newQuantity) => {
            try {
                console.log("📈 Updating cart item quantity:", { itemKey, newQuantity });

                if (newQuantity <= 0) {
                    return { success: false, error: "Quantity must be greater than 0" };
                }

                const variables = {
                    items: [
                        {
                            key: itemKey,
                            quantity: parseInt(newQuantity),
                        },
                    ],
                };

                const { data, errors } = await updateCartMutation({ variables });

                if (errors?.length > 0) {
                    throw new Error(errors[0]?.message || "Failed to update cart");
                }

                if (data?.updateItemQuantities?.cart) {
                    console.log("✅ Cart quantity updated successfully");
                    return {
                        success: true,
                        cart: data.updateItemQuantities.cart,
                        action: "updated",
                    };
                }

                throw new Error("No cart data returned");
            } catch (error) {
                console.error("❌ Update cart quantity error:", error);
                return {
                    success: false,
                    error: error.message || "Failed to update cart quantity",
                };
            }
        },
        [updateCartMutation]
    );

    const clearCart = useCallback(() => {
        console.log("🗑️ Clearing cart");
        removeFromLocalStorage("woocommerce_cart_items");
        removeFromLocalStorage("cart_session_token");
        setCartData(null);
        setCartSessionToken(null);
    }, [removeFromLocalStorage]);

    const refreshCart = useCallback(async () => {
        console.log("🔄 Refreshing cart...");
        
        try {
            const result = await getCartQuery();

            if (result.data?.cart) {
                console.log("✅ Cart refreshed from server");
                return result.data.cart;
            }
        } catch (error) {
            console.error("❌ Error refreshing cart:", error);
            // Fallback to localStorage
            const localCart = loadCartFromLocalStorage();
            setCartData(localCart);
            return localCart;
        }
        return null;
    }, [getCartQuery, loadCartFromLocalStorage]);

    // Computed values
    const cartItemCount = useMemo(() => {
        return cartData?.contents?.nodes?.reduce((total, item) => total + (item.quantity || 0), 0) || 0;
    }, [cartData]);

    const cartItems = useMemo(() => {
        return cartData?.contents?.nodes || [];
    }, [cartData]);

    const cartTotal = useMemo(() => {
        return cartData?.total || "0";
    }, [cartData]);

    // Context value
    const value = useMemo(
        () => ({
            // Auth data
            user: authState.user,
            customer: authState.customer,
            tokens: authState.tokens,
            customerId,
            isAuthenticated: !!customerId,
            isGuest: !customerId,
            authLoading: authState.isLoading,

            // Auth functions
            login,
            logout,
            refreshAuth,

            // Cart data
            cart: cartData,
            cartItemCount,
            cartItems,
            cartTotal,
            cartLoading: getCartLoading || addToCartLoading || updateCartLoading,
            isCartInitialized,
            cartSessionToken,

            // Cart functions
            addToCart,
            updateCartItemQuantity,
            clearCart,
            refreshCart,
            findCartItem,
            getCartItemCount: () => cartItemCount,
            getCartItems: () => cartItems,
            getCartTotal: () => cartTotal,

            // Loading state
            loading: authState.isLoading || !isCartInitialized,
        }),
        [
            authState,
            customerId,
            login,
            logout,
            refreshAuth,
            cartData,
            cartItemCount,
            cartItems,
            cartTotal,
            getCartLoading,
            addToCartLoading,
            updateCartLoading,
            isCartInitialized,
            cartSessionToken,
            addToCart,
            updateCartItemQuantity,
            clearCart,
            refreshCart,
            findCartItem,
        ]
    );

    return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

// Rest of the hooks remain the same...
export const useApp = () => {
    const context = useContext(AppContext);
    if (!context) {
        throw new Error("useApp must be used within an AppProvider");
    }
    return context;
};

export const useAuth = () => {
    const { user, customer, tokens, customerId, isAuthenticated, authLoading, login, logout, refreshAuth } = useApp();

    return {
        user,
        customer,
        tokens,
        customerId,
        isAuthenticated,
        isLoading: authLoading,
        login,
        logout,
        refreshAuth,
    };
};

export const useCart = () => {
    const {
        cart,
        cartItemCount,
        cartItems,
        cartTotal,
        cartLoading,
        isCartInitialized,
        addToCart,
        updateCartItemQuantity,
        clearCart,
        refreshCart,
        findCartItem,
        getCartItemCount,
        getCartItems,
        getCartTotal,
        customerId,
        isAuthenticated,
        isGuest,
        cartSessionToken,
    } = useApp();

    return {
        cart,
        cartItemCount,
        cartItems,
        cartTotal,
        loading: cartLoading,
        isInitialized: isCartInitialized,
        addToCart,
        updateCartItemQuantity,
        clearCart,
        refreshCart,
        findCartItem,
        getCartItemCount,
        getCartItems,
        getCartTotal,
        customerId,
        isAuthenticated,
        isGuest,
        cartSessionToken,
    };
};