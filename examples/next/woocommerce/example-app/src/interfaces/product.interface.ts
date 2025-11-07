export interface Product {
	// Basic product info
	id: string;
	databaseId: number;
	name: string;
	slug: string;
	uri: string;
	sku?: string;
	type: string;
	purchasable: boolean;
	// Content
	description?: string;
	shortDescription?: string;

	// Pricing
	price?: string;
	regularPrice?: string;
	salePrice?: string;
	onSale: boolean;

	// Inventory
	stockStatus: "IN_STOCK" | "OUT_OF_STOCK" | "ON_BACKORDER";
	stockQuantity?: number | null;
	totalSales?: number | null;

	// Reviews
	averageRating: number;
	reviewCount: number;

	// Physical properties
	weight?: string;
	length?: string;
	width?: string;
	height?: string;
	externalUrl?: string;
	//variations
	variations?: {
		nodes: Array<{
			id: string;
			databaseId: number;
			name: string;
			slug: string;
			price?: string;
			regularPrice?: string;
			salePrice?: string;
			onSale: boolean;
			stockStatus: "IN_STOCK" | "OUT_OF_STOCK" | "ON_BACKORDER";
			image?: {
				id: string;
				sourceUrl: string;
				altText: string;
			};
			globalAttributes: {
				nodes: Array<{
					name: string;
					label: string;
					options: Array<string>;
					variation: boolean;
					visible: boolean;
				}>;
			};
			attributes: {
				nodes: Array<{
					id: string;
					attributeId: number;
					label: string;
					name: string;
					value: string;
				}>;
			};
		}>;
	};
	// Media
	image?: {
		id: string;
		sourceUrl: string;
		altText: string;
		mediaDetails?: any;
	};
	galleryImages: {
		nodes: Array<{
			id: string;
			sourceUrl: string;
			altText: string;
		}>;
	};

	// Taxonomy
	attributes: {
		nodes: Array<{
			id: string;
			name: string;
			options?: string[];
		}>;
	};
	productCategories: {
		nodes: Array<{
			id: string;
			name: string;
			slug: string;
		}>;
	};
	productTags: {
		nodes: Array<{
			id: string;
			name: string;
			slug: string;
		}>;
	};
}
export interface ProductPrices {
	price: string;
	regularPrice: string;
	salePrice: string;
	onSale: boolean;
}
// Simple cart item interface
export interface CartItem {
	key: string;
	product: Product;
	quantity: number;
	subtotal: string;
	total: string;
}
