export interface CartImage {
	id: string;
	sourceUrl: string;
	altText: string;
}

export interface CartProductNode {
	id: string;
	databaseId: number;
	name: string;
	slug: string;
	type: string;
	image: CartImage;
}

export interface CartProduct {
	node: CartProductNode;
}

export interface CartVariationAttribute {
	name: string;
	value: string;
}

export interface CartVariationNode {
	id: string;
	databaseId: number;
	name: string;
	image: {
		sourceUrl: string;
		altText: string;
	};
	attributes: {
		nodes: CartVariationAttribute[];
	};
}

export interface CartVariation {
	node: CartVariationNode;
}

export interface CartItem {
	key: string;
	quantity: number;
	total: string;
	subtotal: string;
	subtotalTax: string;
	product: CartProduct;
	variation?: CartVariation;
}

export interface MiniCartItem {
	key: string;
	quantity: number;
	subtotal: string;
	subtotalTax: string;
	total: string;
	product: {
		node: {
			name: string;
			image: CartImage;
		};
	};
}

export interface CartContents {
	itemCount: number;
	productCount?: number;
	nodes: CartItem[];
}

export interface MiniCartContents {
	itemCount: number;
	nodes: MiniCartItem[];
}

export interface AppliedCoupon {
	code: string;
	discountAmount: string;
	description?: string;
}

export interface Cart {
	contents: CartContents;
	appliedCoupons: AppliedCoupon[];
	subtotal: string;
	subtotalTax: string;
	shippingTotal: string;
	shippingTax: string;
	discountTotal: string;
	discountTax: string;
	total: string;
	totalTax: string;
	feeTax: string;
	feeTotal: string;
}
export interface MiniCart {
	contents: CartContents;
	appliedCoupons: AppliedCoupon[];
	subtotal: string;
	subtotalTax: string;
	shippingTotal: string;
	shippingTax: string;
	discountTotal: string;
	discountTax: string;
	total: string;
	totalTax: string;
}
export interface MiniCartProps {
	contents: MiniCartContents;
	subtotal: string;
	totalTax: string;
	total: string;
}

export interface MiniCartVisualProps {
	isVisible?: boolean;
	onClose?: () => void;
}
// GraphQL Response Types
export interface GetCartResponse {
	cart: Cart;
}

export interface GetMiniCartResponse {
	cart: MiniCart;
}

export interface UpdateItemQuantitiesResponse {
	updateItemQuantities: {
		updated: Array<{
			key: string;
			quantity: number;
			total: string;
			subtotal: string;
		}>;
		cart: MiniCart;
	};
}

export interface RemoveItemsFromCartResponse {
	removeItemsFromCart: {
		cartItems: Array<{
			key: string;
			quantity: number;
		}>;
		cart: {
			contents: {
				nodes: Array<{
					key: string;
					quantity: number;
					total: string;
					subtotal: string;
				}>;
			};
			subtotal: string;
			total: string;
			totalTax: string;
			contentsTotal: string;
			contentsTax: string;
		};
	};
}

export interface AddToCartResponse {
	addToCart: {
		cart: Cart;
		cartItem: CartItem;
	};
}

export interface ApplyCouponResponse {
	applyCoupon: {
		cart: Cart;
	};
}

export interface RemoveCouponsResponse {
	removeCoupons: {
		cart: Cart;
	};
}

// Cart Provider Context Type
// Update the CartContextType interface
export interface CartContextType {
	cart: Cart | MiniCart | null;
	cartItemCount: number;
	cartItems: CartItem[] | MiniCartItem[];
	cartTotal: string;
	isCartInitialized: boolean;
	cartLoading: boolean;
	clearingCart: boolean; // ✅ Add this
	addToCart: (productId: number, quantity?: number, variationId?: number | null) => Promise<any>;
	updateCartItemQuantity: (itemKey: string, quantity: number) => Promise<any>;
	removeItem: (itemKey: string) => Promise<any>;
	clearCart: () => Promise<any>; // ✅ Update return type
	refreshCart: () => Promise<void>;
	findCartItem: (productId: number, variationId?: number | null) => CartItem | MiniCartItem | undefined;
}
