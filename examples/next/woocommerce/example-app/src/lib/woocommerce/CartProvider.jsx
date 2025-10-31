import React, { createContext, useContext, useEffect, useState, useMemo, useCallback } from "react";
import { useMutation, useLazyQuery } from "@apollo/client";
import { useAuth } from "../auth/AuthProvider";
import useLocalStorage from "../storage";
import {
	AddToCart,
	GET_MINI_CART,
	UPDATE_ITEM_QUANTITIES,
	REMOVE_ITEMS_FROM_CART,
	EMPTY_CART,
	APPLY_COUPON,
	REMOVE_COUPONS,
} from "@/lib/woocommerce/graphQL";

const CartContext = createContext(undefined);

export function CartProvider({ children }) {
	const storage = useLocalStorage;
	const { user, isLoading: authLoading } = useAuth();

	const [cartData, setCartData] = useState(null);
	const [isCartInitialized, setIsCartInitialized] = useState(false);
	const [clearingCart, setClearingCart] = useState(false); // ✅ Add clearing state

	// Cart Mutations
	const [addToCartMutation, { loading: addToCartLoading }] = useMutation(AddToCart, {
		onCompleted: (data) => {
			if (data?.addToCart?.cart) {
				setCartData(data.addToCart.cart);
				storage.saveCartToLocalStorage(data.addToCart.cart);
			}
		},
		onError: (error) => console.error("❌ Add to cart error:", error),
	});

	const [updateCartMutation, { loading: updateCartLoading }] = useMutation(UPDATE_ITEM_QUANTITIES, {
		onCompleted: (data) => {
			if (data?.updateItemQuantities?.cart) {
				setCartData(data.updateItemQuantities.cart);
				storage.saveCartToLocalStorage(data.updateItemQuantities.cart);
			}
		},
		onError: (error) => console.error("❌ Update cart error:", error),
	});

	const [getMiniCartQuery, { loading: getCartLoading }] = useLazyQuery(GET_MINI_CART, {
		onCompleted: (data) => {
			if (data?.cart) {
				setCartData(data.cart);
				storage.saveCartToLocalStorage(data.cart);
			}
		},
		onError: (error) => console.error("❌ GetCart error:", error),
		fetchPolicy: "network-only",
		errorPolicy: "all",
	});

	const [removeItemsMutation, { loading: removeItemLoading }] = useMutation(REMOVE_ITEMS_FROM_CART, {
		onCompleted: (data) => {
			if (data?.removeItemsFromCart?.cart) {
				setCartData(data.removeItemsFromCart.cart);
				storage.saveCartToLocalStorage(data.removeItemsFromCart.cart);
			}
		},
		onError: (error) => console.error("❌ Remove item error:", error),
	});

	const [emptyCartMutation] = useMutation(EMPTY_CART, {
		onCompleted: (data) => {
			console.log("✅ Cart emptied:", data);
			setCartData(null);
			storage.removeItem("woocommerce_cart");
		},
		onError: (error) => console.error("❌ Empty cart error:", error),
	});
	const [applyCouponMutation, { loading: applyCouponLoading }] = useMutation(APPLY_COUPON);
	const [removeCouponsMutation, { loading: removeCouponsLoading }] = useMutation(REMOVE_COUPONS);
	// Initialize Cart when auth changes
	useEffect(() => {
		let isMounted = true;

		const initializeCart = async () => {
			console.log("🛒 Initializing cart...", { user, isCartInitialized });

			const localCart = storage.loadCartFromLocalStorage();

			if (!user) {
				// Guest user
				if (isMounted) {
					setCartData(localCart || null);
					setIsCartInitialized(true);
				}
			} else {
				// Authenticated user
				try {
					const result = await getMiniCartQuery();
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

		if (!authLoading && !isCartInitialized) {
			initializeCart();
		}

		return () => {
			isMounted = false;
		};
	}, [user, authLoading, isCartInitialized]);

	// Cart functions
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
						input: {
							items: [{ key: itemKey, quantity: parseInt(newQuantity) }],
						},
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

	// ✅ Updated clearCart function with loading state and GraphQL mutation
	const clearCart = useCallback(async () => {
		setClearingCart(true);
		try {
			// If user is authenticated, use GraphQL mutation
			if (user) {
				const { data, errors } = await emptyCartMutation();

				if (errors?.length > 0) {
					throw new Error(errors[0]?.message || "Failed to clear cart");
				}

				await refreshCart();
				return { success: true };
			} else {
				// For guest users, just clear local storage
				storage.removeItem("woocommerce_cart");
				setCartData(null);
				return { success: true };
			}
		} catch (error) {
			console.error("❌ Clear cart error:", error);
			return { success: false, error: error.message || "Failed to clear cart" };
		} finally {
			setClearingCart(false);
		}
	}, [user, emptyCartMutation, storage]);

	const refreshCart = useCallback(async () => {
		try {
			const result = await getMiniCartQuery();
			return result.data?.cart || null;
		} catch (error) {
			console.error("❌ Error refreshing cart:", error);
			const localCart = storage.loadCartFromLocalStorage();
			setCartData(localCart);
			return localCart;
		}
	}, [getMiniCartQuery, storage]);

	const removeItem = useCallback(
		async (itemKey) => {
			try {
				const { data, errors } = await removeItemsMutation({
					variables: {
						input: {
							keys: [itemKey],
						},
					},
				});

				if (errors?.length > 0) {
					throw new Error(errors[0]?.message || "Failed to remove item");
				}

				await refreshCart();
				return { success: true, cart: data?.removeItemsFromCart?.cart };
			} catch (error) {
				console.error("❌ Remove item error:", error);
				return { success: false, error: error.message || "Failed to remove item" };
			}
		},
		[removeItemsMutation, refreshCart]
	);

	const applyCoupon = useCallback(
		async (code) => {
			try {
				const { data, errors } = await applyCouponMutation({
					variables: { code },
				});

				if (errors?.length > 0) {
					throw new Error(errors[0]?.message || "Failed to apply coupon");
				}

				await refreshCart();
				return { success: true, cart: data?.applyCoupon?.cart };
			} catch (error) {
				return { success: false, error: error.message || "Failed to apply coupon" };
			}
		},
		[applyCouponMutation, refreshCart]
	);

	const removeCoupons = useCallback(
		async (codes) => {
			try {
				const { data, errors } = await removeCouponsMutation({
					variables: { codes },
				});

				if (errors?.length > 0) {
					throw new Error(errors[0]?.message || "Failed to remove coupons");
				}

				await refreshCart();
				return { success: true, cart: data?.removeCoupons?.cart };
			} catch (error) {
				return { success: false, error: error.message || "Failed to remove coupons" };
			}
		},
		[removeCouponsMutation, refreshCart]
	);

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
		cartLoading: getCartLoading || addToCartLoading || updateCartLoading || removeItemLoading,
		clearingCart,

		// Functions
		addToCart,
		updateCartItemQuantity,
		clearCart,
		refreshCart,
		findCartItem,
		removeItem,
		applyCoupon,
		removeCoupons,
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
