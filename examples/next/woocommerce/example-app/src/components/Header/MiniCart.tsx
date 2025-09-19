import React, { useState } from "react";
import Image from "next/image";
import { useMutation } from "@apollo/client";
import { useCart } from "@/lib/AppProvider";
import { RemoveItemFromCart, ClearCart } from "@/lib/woocommerce/graphQL";

interface MiniCartProps {
    isVisible?: boolean;
    onClose?: () => void;
}

export default function MiniCart({ isVisible = false, onClose }: MiniCartProps) {
    const { cart, cartItemCount, refreshCart, updateCartItemQuantity } = useCart();
    const [updatingItems, setUpdatingItems] = useState<{ [key: string]: boolean }>({});

    // Only need mutations for remove and clear since we're using AppProvider for quantity updates
    const [removeItemMutation, { loading: removeLoading }] = useMutation(RemoveItemFromCart, {
        onCompleted: (data) => {
            console.log("✅ Item removed:", data);
            refreshCart();
        },
        onError: (error) => {
            console.error("❌ Remove item error:", error);
        },
    });

    const [clearCartMutation, { loading: clearLoading }] = useMutation(ClearCart, {
        onCompleted: (data) => {
            console.log("✅ Cart cleared:", data);
            refreshCart();
        },
        onError: (error) => {
            console.error("❌ Clear cart error:", error);
        },
    });

    // Handle quantity update using AppProvider function
    const handleQuantityUpdate = async (key: string, newQuantity: number) => {
        if (newQuantity < 1) {
            handleRemoveItem(key);
            return;
        }

        setUpdatingItems(prev => ({ ...prev, [key]: true }));

        try {
            const result = await updateCartItemQuantity(key, newQuantity);
            
            if (!result.success) {
                console.error("Failed to update quantity:", result.error);
            }
        } catch (error) {
            console.error("Error updating quantity:", error);
        } finally {
            setUpdatingItems(prev => ({ ...prev, [key]: false }));
        }
    };

    // Handle item removal
    const handleRemoveItem = async (key: string) => {
        setUpdatingItems(prev => ({ ...prev, [key]: true }));

        try {
            await removeItemMutation({
                variables: {
                    key,
                },
            });
        } catch (error) {
            console.error("Error removing item:", error);
        } finally {
            setUpdatingItems(prev => ({ ...prev, [key]: false }));
        }
    };

    // Handle clear cart
    const handleClearCart = async () => {
        if (confirm("Are you sure you want to clear your cart?")) {
            try {
                await clearCartMutation();
            } catch (error) {
                console.error("Error clearing cart:", error);
            }
        }
    };

    if (!isVisible) return null;

    return (
        <>
            {/* Backdrop */}
            <div className="fixed inset-0 bg-black bg-opacity-25 z-40" onClick={onClose} />

            {/* Mini Cart Panel */}
            <div className="fixed top-0 right-0 h-full w-96 bg-white shadow-xl z-50 transform transition-transform duration-300 ease-in-out">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b">
                    <h3 className="text-lg font-semibold text-gray-900">Shopping Cart ({cartItemCount})</h3>
                    <button onClick={onClose} className="p-1 hover:bg-gray-100 rounded-full transition-colors">
                        <svg className="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {/* Cart Content */}
                <div className="flex flex-col h-full">
                    {cart?.contents?.nodes?.length > 0 ? (
                        <>
                            {/* Cart Items */}
                            <div className="flex-1 overflow-y-auto p-4">
                                <ul className="space-y-4">
                                    {cart.contents.nodes.map((item) => {
                                        const isUpdating = updatingItems[item.key];
                                        
                                        return (
                                            <li
                                                key={item.key}
                                                className={`flex items-start gap-3 pb-4 border-b border-gray-100 last:border-b-0 ${
                                                    isUpdating ? 'opacity-60 pointer-events-none' : ''
                                                }`}
                                            >
                                                {/* Product Image */}
                                                {item.product?.node?.image ? (
                                                    <Image
                                                        src={item.product.node.image.sourceUrl}
                                                        alt={item.product.node.name || "Product"}
                                                        width={60}
                                                        height={60}
                                                        className="rounded-md object-cover flex-shrink-0"
                                                    />
                                                ) : (
                                                    <div className="w-15 h-15 bg-gray-200 rounded-md flex items-center justify-center flex-shrink-0">
                                                        <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                strokeLinecap="round"
                                                                strokeLinejoin="round"
                                                                strokeWidth={2}
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                            />
                                                        </svg>
                                                    </div>
                                                )}

                                                {/* Product Details */}
                                                <div className="flex-1 min-w-0">
                                                    <h4 className="text-sm font-medium text-gray-900 truncate">
                                                        {item.product?.node?.name || item.product?.name || "Unknown Product"}
                                                    </h4>

                                                    {/* Variation info if available */}
                                                    {item.variation?.node?.name && (
                                                        <p className="text-xs text-gray-500 mt-1">{item.variation.node.name}</p>
                                                    )}

                                                    {/* Price */}
                                                    <p className="text-sm font-semibold text-green-600 mt-1">{item.total}</p>

                                                    {/* Quantity Controls */}
                                                    <div className="flex items-center justify-between mt-3">
                                                        <div className="flex items-center border border-gray-300 rounded-md">
                                                            <button
                                                                onClick={() => handleQuantityUpdate(item.key, item.quantity - 1)}
                                                                disabled={isUpdating || item.quantity <= 1}
                                                                className="p-1 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                                            >
                                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 12H4" />
                                                                </svg>
                                                            </button>
                                                            <span className="px-2 py-1 text-xs font-medium text-gray-900 min-w-[30px] text-center">
                                                                {item.quantity}
                                                            </span>
                                                            <button
                                                                onClick={() => handleQuantityUpdate(item.key, item.quantity + 1)}
                                                                disabled={isUpdating}
                                                                className="p-1 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                                            >
                                                                <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                </svg>
                                                            </button>
                                                        </div>

                                                        {/* Remove Button */}
                                                        <button
                                                            onClick={() => handleRemoveItem(item.key)}
                                                            disabled={isUpdating}
                                                            className="text-red-600 hover:text-red-800 text-xs font-medium disabled:opacity-50"
                                                        >
                                                            {isUpdating ? "..." : "Remove"}
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </div>

                            {/* Cart Footer */}
                            <div className="border-t bg-gray-50 p-4 space-y-4" style={{ minHeight: "290px" }}>
                                {/* Cart Total */}
                                <div className="flex items-center justify-between">
                                    <span className="text-lg font-semibold text-gray-900">Total:</span>
                                    <span className="text-lg font-bold text-blue-600">{cart.total}</span>
                                </div>

                                {/* Action Buttons */}
                                <div className="space-y-2">
                                       <button onClick={() => {
                                refreshCart()
                            }}>Refresh</button>
                                    <button
                                        onClick={() => {
                                            onClose?.();
                                            window.location.href = "/cart";
                                        }}
                                        className="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition-colors font-medium"
                                    >
                                        View Cart
                                    </button>
                                    <button
                                        onClick={() => {
                                            onClose?.();
                                            window.location.href = "/checkout";
                                        }}
                                        className="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 transition-colors font-medium"
                                    >
                                        Checkout
                                    </button>
                                    <button
                                        onClick={handleClearCart}
                                        disabled={clearLoading}
                                        className="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition-colors font-medium text-sm disabled:opacity-50"
                                    >
                                        {clearLoading ? "Clearing..." : "Clear Cart"}
                                    </button>
                                </div>
                            </div>
                        </>
                    ) : (
                        /* Empty Cart */
                        <div className="flex-1 flex flex-col items-center justify-center p-8 text-center">
                            <div className="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={1.5}
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                            <p className="text-gray-500 mb-6">Add some products to get started!</p>
                            <button
                                onClick={() => {
                                    onClose?.();
                                    window.location.href = "/";
                                }}
                                className="bg-blue-600 text-white py-2 px-6 rounded-md hover:bg-blue-700 transition-colors font-medium"
                            >
                                Continue Shopping
                            </button>
                            <button onClick={() => {
                                refreshCart()
                            }}>Refresh</button>
                        </div>
                    )}
                </div>

                {/* Loading Overlay */}
                {(removeLoading || clearLoading) && (
                    <div className="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                        <div className="flex items-center space-x-2">
                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <span className="text-sm text-gray-600">Updating cart...</span>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}