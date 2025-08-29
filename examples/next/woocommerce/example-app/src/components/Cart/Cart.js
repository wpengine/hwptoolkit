import React, { useState } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useCartQuery } from '@/lib/woocommerce/cartQuery';
import { useCartMutations, useOtherCartMutations } from '@/lib/woocommerce/cart';

export default function Cart() {
  const { data, loading, error, refetch } = useCartQuery();
  const { updateItemQuantities, removeItemsFromCart, loading: mutationLoading } = useCartMutations();
  const { applyCoupon, removeCoupons, loading: couponLoading } = useOtherCartMutations();
  
  const [couponCode, setCouponCode] = useState('');
  const [updatingItems, setUpdatingItems] = useState({});

  const cart = data?.cart;
  const cartItems = cart?.contents?.nodes || [];


  console.log('Cart data:', cart);
  console.log('Cart error:', error);


  const handleQuantityUpdate = async (cartKey, newQuantity) => {
    if (newQuantity < 1) {
      handleRemoveItem(cartKey);
      return;
    }

    setUpdatingItems(prev => ({ ...prev, [cartKey]: true }));

    try {
      const { data } = await updateItemQuantities({
        variables: {
          input: {
            items: [
              {
                key: cartKey,
                quantity: newQuantity,
              },
            ],
          },
        },
      });

      if (data?.updateItemQuantities) {
        await refetch();
      }
    } catch (error) {
      console.error('Error updating quantity:', error);
    } finally {
      setUpdatingItems(prev => ({ ...prev, [cartKey]: false }));
    }
  };


  const handleRemoveItem = async (cartKey) => {
    setUpdatingItems(prev => ({ ...prev, [cartKey]: true }));

    try {
      const { data } = await removeItemsFromCart({
        variables: {
          input: {
            keys: [cartKey],
          },
        },
      });

      if (data?.removeItemsFromCart) {
        await refetch();
      }
    } catch (error) {
      console.error('Error removing item:', error);
    } finally {
      setUpdatingItems(prev => ({ ...prev, [cartKey]: false }));
    }
  };


  const handleApplyCoupon = async (e) => {
    e.preventDefault();
    if (!couponCode.trim()) return;

    try {
      const { data } = await applyCoupon({
        variables: {
          input: {
            code: couponCode,
          },
        },
      });

      if (data?.applyCoupon) {
        setCouponCode('');
        await refetch();
      }
    } catch (error) {
      console.error('Error applying coupon:', error);
    }
  };

  
  const handleRemoveCoupon = async (couponCode) => {
    try {
      const { data } = await removeCoupons({
        variables: {
          input: {
            codes: [couponCode],
          },
        },
      });

      if (data?.removeCoupons) {
        await refetch();
      }
    } catch (error) {
      console.error('Error removing coupon:', error);
    }
  };

  if (loading) return <div className="cart-loading">Loading cart...</div>;
  
  if (error) {
    console.error('Cart GraphQL Error:', error);
    return (
      <div className="cart-error">
        <h2>Error loading cart</h2>
        <p>{error.message}</p>
        <button onClick={() => refetch()}>Try Again</button>
      </div>
    );
  }
  
  if (!cart || cart.isEmpty) {
    return (
      <div className="empty-cart">
        <h2>Your cart is empty</h2>
        <p>Add some products to get startedd!</p>
        <Link href="/shop" className="continue-shopping-btn">
          Continue Shopping
        </Link>
      </div>
    );
  }

  return (
    <div className="cart-page">
      <div className="cart-container">
        <h1>Shopping Cart</h1>

        {/* Cart Items */}
        <div className="cart-items">
          {cartItems.map((item) => {
            const product = item.product.node;
            const variation = item.variation?.node;
            const isUpdating = updatingItems[item.key];

            return (
              <div key={item.key} className={`cart-item ${isUpdating ? 'updating' : ''}`}>
                {/* Product Image */}
                <div className="item-image">
                  <Image
                    src={variation?.image?.sourceUrl || product.image?.sourceUrl || '/placeholder.jpg'}
                    alt={variation?.image?.altText || product.image?.altText || product.name}
                    width={100}
                    height={100}
                    className="product-image"
                  />
                </div>

                {/* Product Info */}
                <div className="item-info">
                  <Link href={`/product/${product.slug}`} className="product-name">
                    {product.name}
                  </Link>
                  {variation && (
                    <p className="variation-name">{variation.name}</p>
                  )}
                  <p className="item-price">
                    {variation?.price || product.price}
                  </p>
                </div>

                {/* Quantity Controls */}
                <div className="quantity-controls">
                  <button
                    onClick={() => handleQuantityUpdate(item.key, item.quantity - 1)}
                    disabled={isUpdating || item.quantity <= 1}
                    className="quantity-btn"
                  >
                    -
                  </button>
                  <span className="quantity">{item.quantity}</span>
                  <button
                    onClick={() => handleQuantityUpdate(item.key, item.quantity + 1)}
                    disabled={isUpdating}
                    className="quantity-btn"
                  >
                    +
                  </button>
                </div>

                {/* Item Total */}
                <div className="item-total">
                  <span className="total-price">{item.total}</span>
                  <button
                    onClick={() => handleRemoveItem(item.key)}
                    disabled={isUpdating}
                    className="remove-btn"
                  >
                    {isUpdating ? 'Removing...' : 'Remove'}
                  </button>
                </div>
              </div>
            );
          })}
        </div>

        {/* Cart Summary */}
        <div className="cart-summary">
          <div className="summary-section">
            <h3>Order Summary</h3>
            
            <div className="summary-row">
              <span>Subtotal:</span>
              <span>{cart.subtotal}</span>
            </div>
            
            {cart.discountTotal && cart.discountTotal !== "0" && (
              <div className="summary-row discount">
                <span>Discount:</span>
                <span>-{cart.discountTotal}</span>
              </div>
            )}
            
            {cart.shippingTotal && cart.shippingTotal !== "0" && (
              <div className="summary-row">
                <span>Shipping:</span>
                <span>{cart.shippingTotal}</span>
              </div>
            )}
            
            {cart.totalTax && cart.totalTax !== "0" && (
              <div className="summary-row">
                <span>Tax:</span>
                <span>{cart.totalTax}</span>
              </div>
            )}
            
            <div className="summary-row total">
              <span>Total:</span>
              <span>{cart.total}</span>
            </div>
          </div>

          {/* Applied Coupons */}
          {cart.appliedCoupons && cart.appliedCoupons.length > 0 && (
            <div className="applied-coupons">
              <h4>Applied Coupons</h4>
              {cart.appliedCoupons.map((coupon) => (
                <div key={coupon.code} className="coupon-item">
                  <span>{coupon.code}</span>
                  <span>-{coupon.discountAmount}</span>
                  <button
                    onClick={() => handleRemoveCoupon(coupon.code)}
                    disabled={couponLoading}
                    className="remove-coupon-btn"
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
          )}

          {/* Coupon Form */}
          <form onSubmit={handleApplyCoupon} className="coupon-form">
            <input
              type="text"
              placeholder="Coupon code"
              value={couponCode}
              onChange={(e) => setCouponCode(e.target.value)}
              className="coupon-input"
            />
            <button
              type="submit"
              disabled={couponLoading || !couponCode.trim()}
              className="apply-coupon-btn"
            >
              {couponLoading ? 'Applying...' : 'Apply'}
            </button>
          </form>

          {/* Checkout Button */}
          <Link href="/checkout" className="checkout-btn">
            Proceed to Checkout
          </Link>
        </div>
      </div>

      <style jsx>{`
        .cart-page {
          max-width: 1200px;
          margin: 0 auto;
          padding: 20px;
        }

        .cart-container h1 {
          margin-bottom: 30px;
          color: #2c3e50;
        }

        .cart-items {
          background: white;
          border-radius: 8px;
          padding: 20px;
          margin-bottom: 30px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cart-item {
          display: grid;
          grid-template-columns: 100px 1fr auto auto;
          gap: 20px;
          align-items: center;
          padding: 20px 0;
          border-bottom: 1px solid #eee;
          transition: opacity 0.3s ease;
        }

        .cart-item:last-child {
          border-bottom: none;
        }

        .cart-item.updating {
          opacity: 0.6;
        }

        .item-image {
          border-radius: 8px;
          overflow: hidden;
        }

        .product-image {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .item-info {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }

        .product-name {
          font-size: 16px;
          font-weight: 600;
          color: #2c3e50;
          text-decoration: none;
        }

        .product-name:hover {
          color: #3498db;
        }

        .variation-name {
          font-size: 14px;
          color: #7f8c8d;
          margin: 0;
        }

        .item-price {
          font-size: 16px;
          font-weight: bold;
          color: #27ae60;
          margin: 0;
        }

        .quantity-controls {
          display: flex;
          align-items: center;
          gap: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
          padding: 5px;
        }

        .quantity-btn {
          background: #f8f9fa;
          border: none;
          width: 30px;
          height: 30px;
          border-radius: 4px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 16px;
          font-weight: bold;
        }

        .quantity-btn:hover:not(:disabled) {
          background: #e9ecef;
        }

        .quantity-btn:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        .quantity {
          font-weight: 600;
          min-width: 20px;
          text-align: center;
        }

        .item-total {
          text-align: right;
          display: flex;
          flex-direction: column;
          gap: 10px;
        }

        .total-price {
          font-size: 18px;
          font-weight: bold;
          color: #2c3e50;
        }

        .remove-btn {
          background: #e74c3c;
          color: white;
          border: none;
          padding: 8px 12px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 12px;
        }

        .remove-btn:hover:not(:disabled) {
          background: #c0392b;
        }

        .remove-btn:disabled {
          background: #bdc3c7;
          cursor: not-allowed;
        }

        .cart-summary {
          background: white;
          border-radius: 8px;
          padding: 20px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-section h3 {
          margin-bottom: 20px;
          color: #2c3e50;
        }

        .summary-row {
          display: flex;
          justify-content: space-between;
          margin-bottom: 10px;
          padding: 5px 0;
        }

        .summary-row.total {
          border-top: 2px solid #eee;
          margin-top: 15px;
          padding-top: 15px;
          font-size: 18px;
          font-weight: bold;
        }

        .summary-row.discount {
          color: #e74c3c;
        }

        .applied-coupons {
          margin: 20px 0;
          padding: 15px;
          background: #f8f9fa;
          border-radius: 4px;
        }

        .applied-coupons h4 {
          margin-bottom: 10px;
          color: #2c3e50;
        }

        .coupon-item {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 5px;
        }

        .remove-coupon-btn {
          background: #e74c3c;
          color: white;
          border: none;
          border-radius: 50%;
          width: 20px;
          height: 20px;
          cursor: pointer;
          font-size: 12px;
        }

        .coupon-form {
          display: flex;
          gap: 10px;
          margin: 20px 0;
        }

        .coupon-input {
          flex: 1;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
        }

        .apply-coupon-btn {
          background: #3498db;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 4px;
          cursor: pointer;
          font-weight: 600;
        }

        .apply-coupon-btn:hover:not(:disabled) {
          background: #2980b9;
        }

        .apply-coupon-btn:disabled {
          background: #bdc3c7;
          cursor: not-allowed;
        }

        .checkout-btn {
          display: block;
          width: 100%;
          background: #27ae60;
          color: white;
          text-align: center;
          padding: 15px;
          border-radius: 4px;
          text-decoration: none;
          font-weight: 600;
          font-size: 16px;
          margin-top: 20px;
        }

        .checkout-btn:hover {
          background: #229954;
        }

        .empty-cart {
          text-align: center;
          padding: 60px 20px;
        }

        .continue-shopping-btn {
          display: inline-block;
          background: #3498db;
          color: white;
          padding: 12px 24px;
          border-radius: 4px;
          text-decoration: none;
          margin-top: 20px;
        }

        .cart-loading,
        .cart-error {
          text-align: center;
          padding: 40px 20px;
        }

        .cart-error button {
          background: #3498db;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 4px;
          cursor: pointer;
          margin-top: 10px;
        }

        @media (max-width: 768px) {
          .cart-item {
            grid-template-columns: 80px 1fr;
            gap: 15px;
          }

          .quantity-controls,
          .item-total {
            grid-column: 1 / -1;
            justify-self: stretch;
          }

          .quantity-controls {
            justify-content: center;
            margin-top: 10px;
          }

          .item-total {
            text-align: center;
            margin-top: 10px;
          }
        }
      `}</style>
    </div>
  );
}