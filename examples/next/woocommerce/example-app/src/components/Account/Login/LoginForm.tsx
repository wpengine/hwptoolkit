import React, { useState } from "react";
import { useAuth } from "@/lib/providers/AuthProvider";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";

export default function LoginForm() {
    const { login } = useAuth();
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState("");
    const [success, setSuccess] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault(); // ✅ Prevent page refresh
        
        setError("");
        setSuccess(false);
        setLoading(true);

        try {
            const result = await login(username, password);
            if (result?.success) {
                setSuccess(true);
                setUsername("");
                setPassword("");
            } else {
                setError("Login failed. Please try again.");
                console.error("❌ Login failed - no success flag");
            }
        } catch (err) {
            console.error("❌ Login error caught:", err);
            
            // ✅ Extract error message from different error formats
            let errorMessage = "An error occurred during login";
            
            if (err.graphQLErrors && err.graphQLErrors.length > 0) {
                errorMessage = err.graphQLErrors[0].message;
                console.error("❌ GraphQL Error:", err.graphQLErrors[0]);
            } else if (err.networkError) {
                errorMessage = "Network error. Please check your connection.";
                console.error("❌ Network Error:", err.networkError);
            } else if (err.message) {
                errorMessage = err.message;
            }
            
            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-md mx-auto p-4">
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="username" className="block mb-1 font-medium text-gray-700">
                        Username or Email
                    </label>
                    <input
                        id="username"
                        type="text"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your username or email"
                        required
                        disabled={loading}
                    />
                </div>

                <div>
                    <label htmlFor="password" className="block mb-1 font-medium text-gray-700">
                        Password
                    </label>
                    <input
                        id="password"
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter your password"
                        required
                        disabled={loading}
                    />
                </div>

                <button
                    type="submit"
                    disabled={loading}
                    className={`w-full p-3 rounded-md font-semibold transition-colors ${
                        loading 
                            ? "bg-gray-300 text-gray-500 cursor-not-allowed" 
                            : "bg-blue-600 text-white hover:bg-blue-700"
                    }`}
                >
                    {loading ? (
                        <span className="flex items-center justify-center">
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                <path
                                    className="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            Logging in...
                        </span>
                    ) : (
                        "Login"
                    )}
                </button>

                {/* ✅ Success Alert */}
                {success && (
                    <Alert className="bg-green-50 border-green-200">
                        <div className="flex items-center">
                            <svg className="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            <div>
                                <AlertTitle className="text-green-800 font-semibold">Success!</AlertTitle>
                                <AlertDescription className="text-green-700">
                                    You have been logged in successfully.
                                </AlertDescription>
                            </div>
                        </div>
                    </Alert>
                )}

                {/* ✅ Error Alert */}
                {error && (
                    <Alert variant="destructive" className="bg-red-50 border-red-200">
                        <div className="flex items-start">
                            <svg className="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            <div className="flex-1">
                                <AlertTitle className="text-red-800 font-semibold">Login Failed</AlertTitle>
                                <AlertDescription className="text-red-700">{error}</AlertDescription>
                            </div>
                            <button
                                onClick={() => setError("")}
                                className="text-red-500 hover:text-red-700 ml-2"
                                aria-label="Dismiss error"
                            >
                                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fillRule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </button>
                        </div>
                    </Alert>
                )}

                {/* ✅ Debug Info (remove in production) */}
                <div className="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-md text-xs">
                    <p className="font-semibold text-gray-700 mb-1">Debug Info:</p>
                    <p className="text-gray-600">Loading: {loading ? "Yes" : "No"}</p>
                    <p className="text-gray-600">Has Error: {error ? "Yes" : "No"}</p>
                    <p className="text-gray-600">Success: {success ? "Yes" : "No"}</p>
                    {error && <p className="text-red-600 mt-1">Error: {error}</p>}
                </div>
            </form>
        </div>
    );
}