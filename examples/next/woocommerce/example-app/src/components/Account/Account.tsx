import React, { useState } from "react";
import { useQuery } from "@apollo/client";
import Link from "next/link";
import { useAuthAdmin } from "@/lib/auth/AuthProvider";
import LoadingSpinner from "@/components/Loading/LoadingSpinner";
import LoginForm from "@/components/Account/Login/LoginForm";
import useLocalStorage from "@/lib/storage";
import { GET_USER_SETTINGS } from "@/lib/graphQL/userGraphQL";
import Addresses from "./Tabs/Addresses";
import AccountDetails from "./Tabs/AccountDetails";
import Dashboard from "./Tabs/Dashboard";
import Orders from "./Tabs/Orders";

export default function Account() {
    const { user, isLoading: authLoading, logout, refreshAuth } = useAuthAdmin();
    const tokens = useLocalStorage.getFromLocalStorage("authTokens");
    const isAuthenticated = !!user;

    const {
        data,
        loading: userDataLoading,
        error,
        refetch,
    } = useQuery(GET_USER_SETTINGS, {
        skip: !isAuthenticated,
        fetchPolicy: "network-only",
    });

    const customer = data?.customer;
    const [activeTab, setActiveTab] = useState("dashboard");

    if (authLoading || userDataLoading) {
        return <LoadingSpinner />;
    }

    // Not authenticated - show login form
    if (!isAuthenticated) {
        return (
            <div className="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="text-center">
                        <h2 className="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
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
                                    <span className="px-2 bg-white text-gray-500">New to our store?</span>
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
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gray-50 py-12 px-4">
                <div className="max-w-4xl mx-auto">
                    <div className="bg-red-50 border border-red-200 text-red-800 p-4 rounded-md">
                        <p className="font-semibold">Error loading account data</p>
                        <p className="text-sm">{error.message}</p>
                    </div>
                </div>
            </div>
        );
    }

    // Render tab content
    const renderTabContent = () => {
        switch (activeTab) {
            case "dashboard":
                return <Dashboard customer={customer} tokens={tokens} refreshAuth={refreshAuth} />;
            case "orders":
                return <Orders orders={customer?.orders?.nodes || []} />;
            case "addresses":
                return <Addresses billing={customer?.billing} shipping={customer?.shipping} refetch={refetch} />;
            case "account-details":
                return <AccountDetails customer={customer} refetch={refetch} />;
            default:
                return <Dashboard customer={customer} tokens={tokens} refreshAuth={refreshAuth} />;
        }
    };

    // Authenticated - show account dashboard with tabs
    return (
        <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-7xl mx-auto">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-2">
                        My Account
                    </h1>
                    <p className="text-gray-600">
                        Welcome back, {customer?.displayName || customer?.firstName || "User"}
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {/* Sidebar Navigation */}
                    <div className="md:col-span-1">
                        <nav className="bg-white rounded-lg shadow p-4 space-y-2">
                            <button
                                onClick={() => setActiveTab("dashboard")}
                                className={`w-full text-left px-4 py-3 rounded-md transition-colors ${
                                    activeTab === "dashboard"
                                        ? "bg-blue-600 text-white"
                                        : "text-gray-700 hover:bg-gray-100"
                                }`}
                            >
                                <span className="flex items-center">
                                    <svg
                                        className="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                        />
                                    </svg>
                                    Dashboard
                                </span>
                            </button>

                            <button
                                onClick={() => setActiveTab("orders")}
                                className={`w-full text-left px-4 py-3 rounded-md transition-colors ${
                                    activeTab === "orders"
                                        ? "bg-blue-600 text-white"
                                        : "text-gray-700 hover:bg-gray-100"
                                }`}
                            >
                                <span className="flex items-center">
                                    <svg
                                        className="w-5 h-5 mr-3"
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
                                    Orders
                                </span>
                            </button>

                            <button
                                onClick={() => setActiveTab("addresses")}
                                className={`w-full text-left px-4 py-3 rounded-md transition-colors ${
                                    activeTab === "addresses"
                                        ? "bg-blue-600 text-white"
                                        : "text-gray-700 hover:bg-gray-100"
                                }`}
                            >
                                <span className="flex items-center">
                                    <svg
                                        className="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                    Addresses
                                </span>
                            </button>

                            <button
                                onClick={() => setActiveTab("account-details")}
                                className={`w-full text-left px-4 py-3 rounded-md transition-colors ${
                                    activeTab === "account-details"
                                        ? "bg-blue-600 text-white"
                                        : "text-gray-700 hover:bg-gray-100"
                                }`}
                            >
                                <span className="flex items-center">
                                    <svg
                                        className="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    Account Details
                                </span>
                            </button>

                            <hr className="my-2" />

                            <button
                                onClick={logout}
                                className="w-full text-left px-4 py-3 rounded-md text-red-600 hover:bg-red-50 transition-colors"
                            >
                                <span className="flex items-center">
                                    <svg
                                        className="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                        />
                                    </svg>
                                    Logout
                                </span>
                            </button>
                        </nav>
                    </div>

                    {/* Main Content Area */}
                    <div className="md:col-span-3">
                        {renderTabContent()}
                    </div>
                </div>
            </div>
        </div>
    );
}