import React, { useState } from "react";
import LoginForm from "@/components/Account/Login/LoginForm";
import Link from "next/link";
import { useAuth } from "@/lib/auth/AuthProvider";
import { gql, useQuery } from "@apollo/client";

const GET_USER_SETTINGS = gql`
  query GetUserSettings {
    viewer {
      email
      username
      firstName
      lastName
    }
            customer {
        billing {
          firstName
          lastName
          company
          address1
          address2
          city
          state
          country
          postcode
          phone
        }
      }
  }
`;
export const AddressFields = gql`
  fragment AddressFields on CustomerAddress {
    firstName
    lastName
    company
    address1
    address2
    city
    state
    country
    postcode
    phone
  }
`;
const CustomerFieldss = gql`
  query GetUser {
    viewer {
      id
      databaseId
      firstName
      lastName
      displayName
      billing {
        ...AddressFields
      }
      shipping {
        ...AddressFields
      }
      orders(first: 100) {
        nodes {
          ...OrderFields
        }
      }
    }
  }
  ${AddressFields}
`;

export const ProductContentSlice = gql`
  fragment ProductContentSlice on Product {
    id
    databaseId
    name
    slug
    type
    image {
      id
      sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
      altText
    }
    ... on SimpleProduct {
      price
      regularPrice
      soldIndividually
    }
    ... on VariableProduct {
      price
      regularPrice
      soldIndividually
    }
  }
`;

const LineItemFields = gql`
  fragment LineItemFields on LineItem {
    databaseId
    product {
      node {
        ...ProductContentSlice
      }
    }
    orderId
    quantity
    subtotal
    total
    totalTax
  }
  ${ProductContentSlice}
`;

const OrderFields = gql`
  fragment OrderFields on Order {
    id
    databaseId
    orderNumber
    orderVersion
    status
    needsProcessing
    subtotal
    paymentMethodTitle
    total
    totalTax
    date
    dateCompleted
    datePaid
    billing {
      ...AddressFields
    }
    shipping {
      ...AddressFields
    }
    lineItems(first: 100) {
      nodes {
        ...LineItemFields
      }
    }
  }
  ${AddressFields}
  ${LineItemFields}
`;

const CustomerFields = gql`
  fragment CustomerFields on Customer {
    id
    databaseId
    firstName
    lastName
    displayName
    billing {
      ...AddressFields
    }
    shipping {
      ...AddressFields
    }
    orders(first: 100) {
      nodes {
        ...OrderFields
      }
    }
  }
  ${AddressFields}
  ${OrderFields}
`;

export default function Account() {
  const { tokens, isLoading, logout, refreshAuth } = useAuth();
  const isAuthenticated = !!tokens?.authToken;

  const { data, loading: userDataLoading } = useQuery(GET_USER_SETTINGS, {
    skip: !isAuthenticated,
  });
  console.log(data);
  const [activeTab, setActiveTab] = useState("dashboard");

  // Loading state
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  // Not authenticated - show login form
  if (!isAuthenticated) {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div className="sm:mx-auto sm:w-full sm:max-w-md">
          <div className="text-center">
            <h2 className="text-3xl font-bold text-gray-900 mb-2">
              Welcome Back
            </h2>
            <p className="text-gray-600">Sign in to your account to continue</p>
          </div>
        </div>

        <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
          <div className="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
            <LoginForm />

            <div className="mt-6">
              <div className="relative">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-gray-300" />
                </div>
                <div className="relative flex justify-center text-sm">
                  <span className="px-2 bg-white text-gray-500">
                    New to our store?
                  </span>
                </div>
              </div>

              <div className="mt-6">
                <Link
                  href="/register"
                  className="w-full flex justify-center py-3 px-4 border border-blue-600 rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                  Create an Account
                </Link>
              </div>
            </div>
          </div>
        </div>

        {/* Features section */}
        <div className="mt-12 max-w-4xl mx-auto px-4">
          <div className="text-center mb-8">
            <h3 className="text-2xl font-bold text-gray-900 mb-4">
              Why Create an Account?
            </h3>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            <div className="text-center">
              <div className="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg
                  className="w-8 h-8 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                  />
                </svg>
              </div>
              <h4 className="text-lg font-semibold text-gray-900 mb-2">
                Fast Checkout
              </h4>
              <p className="text-gray-600">
                Save your information for quicker purchases next time.
              </p>
            </div>

            <div className="text-center">
              <div className="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg
                  className="w-8 h-8 text-green-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
              </div>
              <h4 className="text-lg font-semibold text-gray-900 mb-2">
                Order History
              </h4>
              <p className="text-gray-600">
                Track your orders and view your purchase history.
              </p>
            </div>

            <div className="text-center">
              <div className="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg
                  className="w-8 h-8 text-purple-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                  />
                </svg>
              </div>
              <h4 className="text-lg font-semibold text-gray-900 mb-2">
                Wishlist
              </h4>
              <p className="text-gray-600">
                Save your favorite items for later purchase.
              </p>
            </div>
          </div>
        </div>
      </div>
    );
  }

  // Authenticated - show account dashboard
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">My Account</h1>
          <button onClick={logout} className="text-red-600 hover:underline">
            Logout
          </button>
        </div>

        <section className="bg-white p-6 rounded-lg shadow mb-8">
          <h2 className="text-xl font-semibold mb-4">Your Account</h2>
          {userDataLoading ? (
            <p>Loading your information...</p>
          ) : data ? (
            <div className="space-y-2">
              <p>
                <strong>Email:</strong> {data.viewer.email}
              </p>
              <p>
                <strong>Username:</strong> {data.viewer.username}
              </p>
            </div>
          ) : (
            <p>Could not load user data</p>
          )}
        </section>

        <section className="bg-white p-6 rounded-lg shadow mb-8">
          <h2 className="text-xl font-semibold mb-4">Authentication Tokens</h2>
          {tokens ? (
            <div className="space-y-2 break-words">
              <p>
                <strong>Auth Token:</strong> {tokens.authToken}
              </p>
              <p>
                <strong>Refresh Token:</strong> {tokens.refreshToken}
              </p>
              <p>
                <strong>Auth Token Expiration:</strong>{" "}
                {tokens.authTokenExpiration}
              </p>
              <p>
                <strong>Refresh Token Expiration:</strong>{" "}
                {tokens.refreshTokenExpiration}
              </p>
            </div>
          ) : (
            <p>No tokens available</p>
          )}
          <button
            onClick={async () => await refreshAuth()}
            className="mt-4 text-blue-600 hover:underline"
          >
            Refresh Tokens
          </button>
        </section>
      </div>
    </div>
  );
}
