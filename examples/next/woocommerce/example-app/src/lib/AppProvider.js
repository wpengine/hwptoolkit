"use client";
import React, { createContext, useContext, useEffect, useState, useMemo, useCallback } from "react";
import { useRouter } from "next/router";
import { useMutation, useLazyQuery } from "@apollo/client";
import useLocalStorage from "./storage";

import {
	AddToCart,
	GetMiniCart,
	UpdateCartItemQuantities,
	LOGIN_MUTATION,
	REFRESH_TOKEN_MUTATION,
} from "@/lib/woocommerce/graphQL";

const AppContext = createContext();

export function AppProvider({ children, storage = useLocalStorage }) {
	const router = useRouter();

	// Simplified Auth State - only store minimal user info
	const [authState, setAuthState] = useState({
		user: null, // { databaseId, email, displayName }
		tokens: null, // { authToken, authTokenExpiration }
		isLoading: true,
	});

	// Cart State
	const [cartData, setCartData] = useState(null);
	const [isCartInitialized, setIsCartInitialized] = useState(false);

	// Get user ID from auth state
	const userId = authState.user?.databaseId || null;

	// Auth Mutations
	const [loginMutation, { loading: loginLoading }] = useMutation(LOGIN_MUTATION);
	const [refreshTokenMutation, { loading: refreshTokenLoading }] = useMutation(REFRESH_TOKEN_MUTATION);

	// Cart Mutations
	const [addToCartMutation, { loading: addToCartLoading }] = useMutation(AddToCart, {
		onCompleted: (data) => {
			console.log("✅ Add to cart completed:", data);
			if (data?.addToCart?.cart) {
				setCartData(data.addToCart.cart);
				saveCartToLocalStorage(data.addToCart.cart);
			}
		},
		onError: (error) => {
			console.error("❌ Add to cart error:", error);
		},
	});

	const [updateCartMutation, { loading: updateCartLoading }] = useMutation(UpdateCartItemQuantities, {
		onCompleted: (data) => {
			console.log("✅ Update cart completed:", data);
			if (data?.updateItemQuantities?.cart) {
				setCartData(data.updateItemQuantities.cart);
				saveCartToLocalStorage(data.updateItemQuantities.cart);
			}
		},
		onError: (error) => {
			console.error("❌ Update cart error:", error);
		},
	});

	const [getCartQuery, { loading: getCartLoading }] = useLazyQuery(GetMiniCart, {
		onCompleted: (data) => {
			if (data?.cart) {
				setCartData(data.cart);
				saveCartToLocalStorage(data.cart);
				console.log("✅ Server cart fetched:", data?.cart.contents.nodes);
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

	const saveCartToLocalStorage = useCallback(
		(cart) => {
			if (cart) {
				console.log("💾 Saving cart to localStorage");
				saveToLocalStorage("woocommerce_cart", cart);
			}
		},
		[saveToLocalStorage]
	);

	const loadCartFromLocalStorage = useCallback(() => {
		return getFromLocalStorage("woocommerce_cart");
	}, [getFromLocalStorage]);

	// Initialize Auth on app start
	useEffect(() => {
		const initializeAuth = () => {
			try {
				console.log("🔐 Initializing auth...");

				const storedTokens = getFromLocalStorage("authTokens");
				const storedUser = getFromLocalStorage("user");
				if (storedTokens && storedUser) {
					setAuthState({
						user: storedUser, // Will be populated on first authenticated query
						tokens: storedTokens,
						isLoading: false,
					});
				} else {
					setAuthState((prev) => ({ ...prev, isLoading: false }));
				}

				//refreshAuth();
				// Check if token is expired
				// if (storedTokens.authTokenExpiration && new Date() > new Date(storedTokens.authTokenExpiration)) {
				// 	console.log("❌ Token expired, clearing");
				// 	refreshAuth();
				// 	removeFromLocalStorage("authTokens");
				// 	setAuthState({ user: null, tokens: null, isLoading: false });
				// } else {
				// 	// Token is valid - we'll fetch user data from the server when needed
				// 	const user = getFromLocalStorage("user");
				// 	setAuthState({
				// 		user: user || null, // Will be populated on first authenticated query
				// 		tokens: storedTokens,
				// 		isLoading: false,
				// 	});
				// }
			} catch (error) {
				console.error("❌ Error initializing auth:", error);
				setAuthState({ user: null, tokens: null, isLoading: false });
			}
		};

		initializeAuth();
	}, [getFromLocalStorage, removeFromLocalStorage]);

	// Initialize Cart after Auth is loaded
	useEffect(() => {
		const initializeCart = async () => {
			if (authState.isLoading || isCartInitialized) return;

			console.log("🛒 === CART INITIALIZATION START ===");
			console.log("👤 User ID:", userId);

			const localCart = loadCartFromLocalStorage();
			const localItemCount = localCart?.contents?.nodes?.length || 0;
			console.log(`📱 localStorage has ${localItemCount} items`);

			if (!userId) {
				// Guest user - use localStorage
				console.log("👤 Guest user - using localStorage cart");

				if (localCart && localItemCount > 0) {
					setCartData(localCart);
				} else {
					setCartData(null);
				}
			} else {
				// Authenticated user - sync with server
				console.log("👤 Authenticated user - syncing with server");

				try {
					const result = await getCartQuery();
					const serverCart = result.data?.cart || null;
					const serverItemCount = serverCart?.contents?.nodes?.length || 0;

					console.log(`🌐 Server has ${serverItemCount} items`);

					if (serverCart) {
						setCartData(serverCart);
						saveCartToLocalStorage(serverCart);
					} else if (localCart) {
						setCartData(localCart);
					} else {
						setCartData(null);
					}
				} catch (error) {
					console.error("❌ Error initializing cart:", error);
					if (localCart) {
						setCartData(localCart);
					}
				}
			}

			console.log("✅ === CART INITIALIZATION COMPLETE ===");
			setIsCartInitialized(true);
		};

		initializeCart();
	}, [authState.isLoading]);

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

			storage.setItem("authTokens", JSON.stringify(newTokens));

			setAuthState((prev) => ({
				...prev,
				tokens: newTokens,
			}));
		} catch (error) {
			console.error("Error refreshing token:", error);
			logout();
		}
	}, [refreshTokenMutation, setAuthState]);

	// Login Function
	const login = useCallback(
		async (username, password) => {
			try {
				console.log("🔐 Starting login...");

				const { data } = await loginMutation({
					variables: { username, password },
				});

				if (!data?.login) {
					throw new Error("Login failed - no data returned");
				}
				const { user, ...tokens } = data.login;

				// Extract minimal user data from LOGIN_MUTATION - graphql
				storage.setItem("authTokens", JSON.stringify(tokens));
				storage.setItem("user", JSON.stringify(user));

				// Update auth state
				setAuthState({
					user,
					tokens,
					isLoading: false,
				});

				// Reset cart to reinitialize for logged-in user
				setIsCartInitialized(false);
				router.push("/my-account");
			} catch (error) {
				console.error("❌ Login error:", error);
				throw error;
			}
		},
		[loginMutation, saveToLocalStorage, router]
	);

	const logout = useCallback(() => {
		console.log("🔐 Logging out...");

		removeFromLocalStorage("user");
		removeFromLocalStorage("authTokens");

		// Update auth state
		setAuthState({
			user: null,
			tokens: null,
			isLoading: false,
		});

		// Keep cart for guest user
		console.log("🛒 Reinitializing cart as guest user");
		setIsCartInitialized(false);

		router.push("/");
	}, [removeFromLocalStorage, router]);

	// Find existing cart item
	const findCartItem = useCallback(
		(productId, variationId = null) => {
			if (!cartData?.contents?.nodes) return null;

			return cartData.contents.nodes.find((item) => {
				const matchesProduct =
					item.product?.node?.databaseId === parseInt(productId) || item.product?.databaseId === parseInt(productId);

				const matchesVariation = variationId
					? item.variation?.node?.databaseId === parseInt(variationId) ||
					  item.variation?.databaseId === parseInt(variationId)
					: !item.variation?.node && !item.variation;

				return matchesProduct && matchesVariation;
			});
		},
		[cartData]
	);

	// Add to cart
	const addToCart = useCallback(
		async (productId, quantity = 1, variationId = null) => {
			try {
				console.log("🛒 Adding to cart:", { productId, quantity, variationId, userId });

				const existingItem = findCartItem(productId, variationId);

				if (existingItem) {
					console.log("📈 Item exists, updating quantity...");

					const newQuantity = existingItem.quantity + quantity;
					const { data, errors } = await updateCartMutation({
						variables: {
							items: [{ key: existingItem.key, quantity: newQuantity }],
						},
					});

					if (errors?.length > 0) {
						throw new Error(errors[0]?.message || "Failed to update cart");
					}

					return {
						success: true,
						cart: data?.updateItemQuantities?.cart,
						action: "updated",
					};
				} else {
					const variables = {
						productId: parseInt(productId),
						quantity: parseInt(quantity),
					};

					if (variationId) {
						variables.variationId = parseInt(variationId);
					}

					console.log("➕ Adding new item");

					const { data, errors } = await addToCartMutation({ variables });

					if (errors?.length > 0) {
						throw new Error(errors[0]?.message || "Failed to add to cart");
					}

					return {
						success: true,
						cart: data?.addToCart?.cart,
						action: "added",
					};
				}
			} catch (error) {
				console.error("❌ Add to cart error:", error);
				return {
					success: false,
					error: error.message || "Failed to add to cart",
				};
			}
		},
		[userId, addToCartMutation, updateCartMutation, findCartItem]
	);

	// Update cart item quantity
	const updateCartItemQuantity = useCallback(
		async (itemKey, newQuantity) => {
			try {
				console.log("📈 Updating cart item quantity:", { itemKey, newQuantity });

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

				return {
					success: true,
					cart: data?.updateItemQuantities?.cart,
					action: "updated",
				};
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
		removeFromLocalStorage("woocommerce_cart");
		setCartData(null);
	}, [removeFromLocalStorage]);

	const refreshCart = useCallback(async () => {
		console.log("🔄 Refreshing cart...");

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
		return cartData?.contents?.itemCount || "0";
	}, [cartData]);

	// Context value
	const value = useMemo(
		() => ({
			// Auth data - minimal
			user: authState.user,
			tokens: authState.tokens,
			authLoading: authState.isLoading || loginLoading,

			// Auth functions
			login,
			logout,

			// Cart data
			cart: cartData,
			cartItemCount,
			cartItems,
			cartTotal,
			cartLoading: getCartLoading || addToCartLoading || updateCartLoading,
			isCartInitialized,

			// Cart functions
			addToCart,
			updateCartItemQuantity,
			clearCart,
			refreshCart,
			findCartItem,

			// Loading state
			loading: authState.isLoading || !isCartInitialized,
		}),
		[
			authState,
			loginLoading,
			login,
			logout,
			cartData,
			cartItemCount,
			cartItems,
			cartTotal,
			getCartLoading,
			addToCartLoading,
			updateCartLoading,
			isCartInitialized,
			addToCart,
			updateCartItemQuantity,
			clearCart,
			refreshCart,
			findCartItem,
		]
	);

	return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export const useApp = () => {
	const context = useContext(AppContext);
	if (!context) {
		throw new Error("useApp must be used within an AppProvider");
	}
	return context;
};

export const useAuth = () => {
	const { user, tokens, authLoading, login, logout, refreshAuth } = useApp();

	return {
		user,
		tokens,
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
	};
};
