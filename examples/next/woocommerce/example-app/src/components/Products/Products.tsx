import { useQuery } from "@apollo/client";
import ProductCard from "@/components/Products/ProductCard";
import React, { useState } from "react";
import { PRODUCTS_QUERY } from "@/lib/graphQL/productGraphQL";

interface ProductsProps {
    count?: number;
    columns?: {
        desktop: number;
        tablet: number;
        mobile: number;
    };
    title?: string;
    showTitle?: boolean;
    displayType?: "recent" | "featured" | "expensive" | "sale" | "rated";
    categoryIn?: string[];
    featured?: boolean;
    queryName?: string;
}

export default function Products({
    count = 12,
    columns = {
        desktop: 4,
        tablet: 3,
        mobile: 2,
    },
    title = "Recent Products",
    showTitle = true,
    displayType = "recent",
    categoryIn = [],
    queryName = "ProductsQuery",
	loadMore = false
}: ProductsProps) {
    const [loadingMore, setLoadingMore] = useState(false);

    const orderByConfig = {
        recent: { field: "DATE", order: "DESC" },
        featured: { order: "DESC", featured: true },
        expensive: { field: "PRICE", order: "DESC" },
        sale: { field: "PRICE", order: "DESC", onSale: true },
        rated: { field: "RATING", order: "ASC" },
    };

    const config = orderByConfig[displayType] || orderByConfig.recent;

    const { data, loading, error, fetchMore } = useQuery(PRODUCTS_QUERY, {
        variables: {
            categoryIn: categoryIn && categoryIn.length > 0 ? categoryIn : null,
            first: count,
            after: null,
            orderByField: config.field,
            orderByOrder: config.order,
            onSale: config.onSale || null,
            featured: config.featured || null,
        },
        notifyOnNetworkStatusChange: true,
    });

    const products = data?.products?.nodes || [];
    const pageInfo = data?.products?.pageInfo;
    const hasNextPage = pageInfo?.hasNextPage;
    const endCursor = pageInfo?.endCursor;

    
    const handleLoadMore = async () => {
        if (!hasNextPage || loadingMore || !loadMore) return;

        setLoadingMore(true);

        try {
            await fetchMore({
                variables: {
                    after: endCursor,
                },
                updateQuery: (prev, { fetchMoreResult }) => {
                    if (!fetchMoreResult) return prev;

                    return {
                        products: {
                            ...fetchMoreResult.products,
                            nodes: [
                                ...prev.products.nodes,
                                ...fetchMoreResult.products.nodes,
                            ],
                        },
                    };
                },
            });
        } catch (err) {
            console.error("Error loading more products:", err);
        } finally {
            setLoadingMore(false);
        }
    };

    if (loading && !loadingMore) {
        return (
            <div className="text-center py-12">
                <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p className="mt-4 text-gray-600">Loading products...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-center py-12">
                <p className="text-red-500">Error loading products: {error.message}</p>
            </div>
        );
    }

    if (!products || products.length === 0) {
        return (
            <div className="text-center py-12">
                <p className="text-gray-500">No {displayType} products found.</p>
            </div>
        );
    }

    const getGridColumns = (cols: number) => `repeat(${cols}, 1fr)`;

    return (
        <div className="recent-products">
            {showTitle && <h2>{title}</h2>}
            
            {/* Products Grid */}
            <div className="products-grid">
                {products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                ))}
            </div>

            {/* Load More Button */}
            {hasNextPage && loadMore && (
                <div className="load-more-container">
                    <button
                        onClick={handleLoadMore}
                        disabled={loadingMore}
                        className="load-more-button"
                    >
                        {loadingMore ? (
                            <>
                                <svg className="spinner" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                                    <path
                                        className="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    />
                                </svg>
                                <span>Loading More...</span>
                            </>
                        ) : (
                            <>
                                <svg className="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Load More Products</span>
                            </>
                        )}
                    </button>
                    <p className="products-count">
                        Showing {products.length} products
                    </p>
                </div>
            )}

            <style jsx>{`
                .recent-products {
                    margin: 40px 0;
                }

                .recent-products h2 {
                    font-size: 28px;
                    margin-bottom: 24px;
                    color: #2c3e50;
                    text-align: center;
                }

                .products-grid {
                    display: grid;
                    grid-template-columns: ${getGridColumns(columns.desktop)};
                    gap: 20px;
                    padding: 0;
                }

                .load-more-container {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 12px;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #e5e7eb;
                }

                .load-more-button {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 32px;
                    background-color: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
                }

                .load-more-button:hover:not(:disabled) {
                    background-color: #2563eb;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
                }

                .load-more-button:disabled {
                    background-color: #9ca3af;
                    cursor: not-allowed;
                    transform: none;
                }

                .load-more-button .icon,
                .load-more-button .spinner {
                    width: 20px;
                    height: 20px;
                }

                .load-more-button .spinner {
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    from {
                        transform: rotate(0deg);
                    }
                    to {
                        transform: rotate(360deg);
                    }
                }

                .products-count {
                    font-size: 14px;
                    color: #6b7280;
                    margin: 0;
                }

                @media (max-width: 1024px) {
                    .products-grid {
                        grid-template-columns: ${getGridColumns(columns.tablet)};
                    }
                }

                @media (max-width: 768px) {
                    .products-grid {
                        grid-template-columns: ${getGridColumns(columns.mobile)};
                        gap: 15px;
                    }

                    .recent-products h2 {
                        font-size: 24px;
                        margin-bottom: 20px;
                    }

                    .load-more-button {
                        width: 100%;
                        justify-content: center;
                    }
                }
            `}</style>
        </div>
    );
}

Products.query = {
    query: PRODUCTS_QUERY,
    variables: ({ displayType = "recent", categoryIn = null, count = 12, rating = [] }) => {
        const orderByConfig = {
            recent: { field: "DATE", order: "DESC" },
            featured: { order: "DESC", featured: true },
            expensive: { field: "PRICE", order: "DESC" },
            sale: { field: "PRICE", order: "DESC", onSale: true },
            rated: { field: "RATING", order: "ASC", rating },
        };

        const config = orderByConfig[displayType] || orderByConfig.recent;

        return {
            categoryIn: categoryIn && categoryIn.length > 0 ? categoryIn : null,
            first: count,
            after: null,
            orderByField: config.field,
            orderByOrder: config.order,
            onSale: config.onSale || null,
            featured: config.featured || null,
            rating: config.rating || null,
        };
    },
};