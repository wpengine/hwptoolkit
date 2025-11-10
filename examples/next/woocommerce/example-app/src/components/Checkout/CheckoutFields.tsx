import { useState } from "react";
import UserField from "@/components/ui/Field";

const billingFields = [
    {
        id: "billing_first_name",
        label: "First Name",
        type: "text",
        required: true,
    },
    {
        id: "billing_last_name",
        label: "Last Name",
        type: "text",
        required: true,
    },
    {
        id: "billing_email",
        label: "Email Address",
        type: "email",
        required: true,
    },
    {
        id: "billing_phone",
        label: "Phone Number",
        type: "tel",
        required: false,
    },
    {
        id: "billing_address_1",
        label: "Address Line 1",
        type: "text",
        required: true,
    },
    {
        id: "billing_address_2",
        label: "Address Line 2",
        type: "text",
        required: false,
    },
    {
        id: "billing_city",
        label: "City",
        type: "text",
        required: true,
    },
    {
        id: "billing_postcode",
        label: "Postal Code",
        type: "text",
        required: true,
    },
    {
        id: "billing_country",
        label: "Country",
        type: "text",
        required: false,
    },
    {
        id: "billing_state",
        label: "State/Province",
        type: "text",
        required: true,
    },
];

const shippingFields = [
    {
        id: "shipping_first_name",
        label: "First Name",
        type: "text",
        required: true,
    },
    {
        id: "shipping_last_name",
        label: "Last Name",
        type: "text",
        required: true,
    },
    {
        id: "shipping_address_1",
        label: "Address Line 1",
        type: "text",
        required: true,
    },
    {
        id: "shipping_address_2",
        label: "Address Line 2",
        type: "text",
        required: false,
    },
    {
        id: "shipping_city",
        label: "City",
        type: "text",
        required: true,
    },
    {
        id: "shipping_postcode",
        label: "Postal Code",
        type: "text",
        required: true,
    },
    {
        id: "shipping_country",
        label: "Country",
        type: "text",
        required: true,
    },
    {
        id: "shipping_state",
        label: "State/Province",
        type: "text",
        required: true,
    },
];

interface CheckoutFieldsProps {
    onDataChange?: (data: CheckoutFormData) => void;
}

export interface CheckoutFormData {
    billing: {
        firstName: string;
        lastName: string;
        email: string;
        phone?: string;
        address1: string;
        address2?: string;
        city: string;
        postcode: string;
        country: string;
        state: string;
    };
    shipping: {
        firstName: string;
        lastName: string;
        address1: string;
        address2?: string;
        city: string;
        postcode: string;
        country: string;
        state: string;
    };
    shipToDifferentAddress: boolean;
}

export default function CheckoutFields({ onDataChange }: CheckoutFieldsProps) {
    const [formData, setFormData] = useState<CheckoutFormData>({
        billing: {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            address1: "",
            address2: "",
            city: "",
            postcode: "",
            country: "",
            state: "",
        },
        shipping: {
            firstName: "",
            lastName: "",
            address1: "",
            address2: "",
            city: "",
            postcode: "",
            country: "",
            state: "",
        },
        shipToDifferentAddress: false,
    });

    const [errors, setErrors] = useState<Record<string, string>>({});

    const handleFieldChange = (fieldId: string, value: string) => {
        // Determine if it's billing or shipping field
        const isBilling = fieldId.startsWith("billing_");
        const section = isBilling ? "billing" : "shipping";
        const fieldName = fieldId.replace(/^(billing|shipping)_/, "");

        // Convert field name to camelCase
        const camelCaseField = fieldName.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());

        const updatedData = {
            ...formData,
            [section]: {
                ...formData[section],
                [camelCaseField]: value,
            },
        };

        setFormData(updatedData);

        // Clear error for this field
        if (errors[fieldId]) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors[fieldId];
                return newErrors;
            });
        }

        // Notify parent component
        if (onDataChange) {
            onDataChange(updatedData);
        }
    };

    const handleShipToDifferentAddress = (checked: boolean) => {
        const updatedData = {
            ...formData,
            shipToDifferentAddress: checked,
        };

        // If unchecked, copy billing to shipping
        if (!checked) {
            updatedData.shipping = {
                firstName: formData.billing.firstName,
                lastName: formData.billing.lastName,
                address1: formData.billing.address1,
                address2: formData.billing.address2,
                city: formData.billing.city,
                postcode: formData.billing.postcode,
                country: formData.billing.country,
                state: formData.billing.state,
            };
        }

        setFormData(updatedData);

        if (onDataChange) {
            onDataChange(updatedData);
        }
    };

    return (
        <>
            <div className="billing-fields">
                <h3 className="text-xl font-semibold text-gray-900 mb-4">Billing Information</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {billingFields.map((field) => (
                        <div key={field.id} className={field.id.includes("address") || field.id.includes("email") ? "md:col-span-2" : ""}>
                            <UserField
                                label={field.label}
                                type={field.type}
                                required={field.required}
                                name={field.id}
                                value={formData.billing[field.id.replace("billing_", "").replace(/_([a-z])/g, (_, l) => l.toUpperCase()) as keyof typeof formData.billing] || ""}
                                onChange={(value) => handleFieldChange(field.id, value)}
                                placeholder={field.label}
                                error={errors[field.id]}
                            />
                        </div>
                    ))}
                </div>
            </div>

            <div className="shipping-fields mt-6 border-t-2 border-gray-200 pt-6">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-xl font-semibold text-gray-900">Shipping Information</h3>
                    <label className="flex items-center space-x-2 cursor-pointer">
                        <input
                            type="checkbox"
                            checked={formData.shipToDifferentAddress}
                            onChange={(e) => handleShipToDifferentAddress(e.target.checked)}
                            className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span className="text-sm text-gray-700">Ship to different address?</span>
                    </label>
                </div>

                {!formData.shipToDifferentAddress ? (
                    <div className="p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p className="text-sm text-blue-800">
                            <svg className="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            Shipping to the same address as billing
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {shippingFields.map((field) => (
                            <div key={field.id} className={field.id.includes("address") ? "md:col-span-2" : ""}>
                                <UserField
                                    label={field.label}
                                    type={field.type}
                                    required={field.required}
                                    name={field.id}
                                    value={formData.shipping[field.id.replace("shipping_", "").replace(/_([a-z])/g, (_, l) => l.toUpperCase()) as keyof typeof formData.shipping] || ""}
                                    onChange={(value) => handleFieldChange(field.id, value)}
                                    placeholder={field.label}
                                    error={errors[field.id]}
                                />
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}