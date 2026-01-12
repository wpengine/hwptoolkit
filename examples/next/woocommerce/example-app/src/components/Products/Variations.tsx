import React from "react";
import Image from "next/image";
import { Product } from "@/interfaces/product.interface";
import ProductPrice from "./Price";

interface ProductVariationsProps {
    variations: Product["variations"];
    globalAttributes: Product["globalAttributes"];
    selectedVariation: Product["variations"]["nodes"][0] | null;
    onVariationSelect: (variation: Product["variations"]["nodes"][0] | null) => void;
    onAttributeSelect: (attributeName: string, attributeValue: string) => void;
    selectedAttributes: { attributeName: string; attributeValue: string }[];
}

export default function ProductVariations({
    variations,
    globalAttributes,
    selectedVariation,
    onVariationSelect,
    onAttributeSelect,
    selectedAttributes = [],
}: ProductVariationsProps) {
    if (!variations || !variations.nodes || variations.nodes.length === 0) {
        return null;
    }

    
    const isAttributeSelected = (attributeName: string, attributeValue: string) => {
        return selectedAttributes.some(
            (attr) => attr.attributeName === attributeName && attr.attributeValue === attributeValue
        );
    };

    const getAvailableOptions = (attributeName: string) => {
        // Get all options from global attributes
        const globalAttr = globalAttributes?.nodes?.find((attr) => attr.name === attributeName);
        if (!globalAttr) return [];

        // Filter variations that match currently selected attributes (excluding the current attribute)
        const otherSelectedAttributes = selectedAttributes.filter((attr) => attr.attributeName !== attributeName);

        if (otherSelectedAttributes.length === 0) {
            // No other attributes selected, show all options
            return globalAttr.options;
        }

        // Find variations that match other selected attributes
        const matchingVariations = variations.nodes.filter((variation) => {
            if (!variation.attributes?.nodes) return false;

            // Check if this variation matches all other selected attributes
            return otherSelectedAttributes.every((selectedAttr) => {
                const varAttr = variation.attributes.nodes.find((attr) => attr.name === selectedAttr.attributeName);
                if (!varAttr || !varAttr.value || varAttr.value.trim() === "") return true;
                return varAttr.value.toLowerCase() === selectedAttr.attributeValue.toLowerCase();
            });
        });

        // Extract unique values for the current attribute from matching variations
        const availableValues = new Set<string>();
        matchingVariations.forEach((variation) => {
            const attr = variation.attributes.nodes.find((a) => a.name === attributeName);
            if (attr && attr.value && attr.value.trim() !== "") {
                availableValues.add(attr.value);
            }
        });

        // Filter global options to only include available values
        return globalAttr.options.filter((option) => availableValues.has(option));
    };

    return (
        <div className="product-variations space-y-6">
            {globalAttributes?.nodes && globalAttributes.nodes.length > 0 && (
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold">Select Options</h3>
                    {globalAttributes.nodes.map((attr) => {
                        const availableOptions = getAvailableOptions(attr.name);

                        return (
                            <div key={attr.name} className="variation-attribute">
                                <h4 className="font-medium text-gray-800 mb-2">{attr.label}:</h4>
                                <div className="flex flex-wrap gap-2">
                                    {attr.options.map((option: string) => {
                                        const isSelected = isAttributeSelected(attr.name, option);
                                        const isAvailable = availableOptions.includes(option);

                                        return (
                                            <button
                                                key={option}
                                                onClick={isAvailable ? () => onAttributeSelect(attr.name, option) : undefined}
                                                disabled={!isAvailable}
                                                className={`px-4 py-2 border rounded-lg text-sm font-medium transition-all ${
                                                    isSelected
                                                        ? "bg-blue-600 text-white border-blue-600"
                                                        : isAvailable
                                                            ? "bg-white hover:bg-gray-50 border-gray-300 text-gray-500"
                                                            : "bg-white border-gray-300 text-gray-400 opacity-50 cursor-not-allowed"
                                                }`}
                                            >
                                                {option}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        );
                    })}

                    {selectedVariation && (
                        <div className="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-blue-800 mb-1">
                                        Selected: {selectedVariation.name}
                                    </p>
                                    {selectedVariation.attributes?.nodes && (
                                        <p className="text-xs text-blue-600">
                                            {selectedVariation.attributes.nodes
                                                .filter((attr) => attr.value && attr.value.trim() !== "")
                                                .map((attr) => `${attr.name}: ${attr.value}`)
                                                .join(", ")}
                                        </p>
                                    )}
                                </div>
                                {selectedVariation.image && (
                                    <div className="relative w-16 h-16 ml-4 flex-shrink-0">
                                        <Image
                                            src={selectedVariation.image.sourceUrl}
                                            alt={selectedVariation.image.altText || selectedVariation.name}
                                            fill
                                            className="object-cover rounded-md"
                                        />
                                    </div>
                                )}
                            </div>
                            {selectedVariation.stockStatus && (
                                <p
                                    className={`text-xs mt-2 font-medium ${
                                        selectedVariation.stockStatus === "IN_STOCK" ? "text-green-600" : "text-red-600"
                                    }`}
                                >
                                    {selectedVariation.stockStatus === "IN_STOCK" ? "✓ In Stock" : "✗ Out of Stock"}
                                </p>
                            )}
                            <div className="mt-2">
                                <ProductPrice
                                    prices={{
                                        onSale: selectedVariation.onSale,
                                        price: selectedVariation.price,
                                        regularPrice: selectedVariation.regularPrice,
                                        salePrice: selectedVariation.salePrice,
                                    }}
                                    size="small"
                                />
                            </div>
                        </div>
                    )}
                </div>
            )}

            <div className="hidden">
                <h3 className="text-lg font-semibold mb-4">All Variations</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {variations.nodes.map((variation) => {
                        const validAttributes = variation.attributes?.nodes?.filter(
                            (attr) => attr.value && attr.value.trim() !== ""
                        ) || [];

                        const currentVariationPrice = {
                            onSale: variation.onSale,
                            price: variation.price,
                            regularPrice: variation.regularPrice,
                            salePrice: variation.salePrice,
                        };

                        return (
                            <div
                                className={`variation border-2 rounded-lg cursor-pointer transition-all hover:shadow-md ${
                                    selectedVariation?.id === variation.id
                                        ? "border-blue-600 bg-blue-50"
                                        : "border-gray-200 hover:border-blue-300"
                                }`}
                                key={variation.id}
                                onClick={() => onVariationSelect(variation)}
                            >
                                <div className="flex items-center p-4">
                                    {variation.image && (
                                        <div className="relative w-20 h-20 flex-shrink-0 mr-4">
                                            <Image
                                                src={variation.image.sourceUrl}
                                                alt={variation.image.altText || variation.name}
                                                fill
                                                className="object-cover rounded-md"
                                            />
                                        </div>
                                    )}
                                    <div className="variation-info flex-1">
                                        <h4 className="variation-title font-semibold text-gray-900 mb-1">{variation.name}</h4>
                                        {validAttributes.length > 0 && (
                                            <p className="text-sm text-gray-600 mb-2">
                                                {validAttributes.map((attr) => attr.value).join(", ")}
                                            </p>
                                        )}
                                        <ProductPrice prices={currentVariationPrice} size="small" />
                                        {variation.stockStatus && (
                                            <p
                                                className={`text-xs mt-1 ${
                                                    variation.stockStatus === "IN_STOCK" ? "text-green-600" : "text-red-600"
                                                }`}
                                            >
                                                {variation.stockStatus === "IN_STOCK" ? "In Stock" : "Out of Stock"}
                                            </p>
                                        )}
                                    </div>
                                    {selectedVariation?.id === variation.id && (
                                        <div className="ml-2">
                                            <svg className="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    fillRule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}