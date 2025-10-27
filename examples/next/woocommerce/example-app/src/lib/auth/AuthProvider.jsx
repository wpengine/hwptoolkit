import React, { createContext, useContext, useEffect, useState } from "react";
import { useNextNavigation } from "../navigation";
import useLocalStorage from "../storage";
import { gql, useMutation } from "@apollo/client";

const LOGIN_MUTATION = gql`
	mutation loginWithPassword($username: String!, $password: String!) {
		login(input: { provider: PASSWORD, credentials: { username: $username, password: $password } }) {
			authToken
			authTokenExpiration
			refreshToken
			refreshTokenExpiration
			user {
				id
				email
				databaseId
			}
			customer {
				databaseId
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
	}
`;

const REFRESH_TOKEN_MUTATION = gql`
	mutation refreshToken($token: String!) {
		refreshToken(input: { refreshToken: $token }) {
			authToken
			authTokenExpiration
			success
		}
	}
`;

const AuthContext = createContext(undefined);

function AuthProvider({ children, storage = useLocalStorage, navigation = useNextNavigation() }) {
	const [loginMutation] = useMutation(LOGIN_MUTATION);
	const [refreshTokenMutation] = useMutation(REFRESH_TOKEN_MUTATION);
	const [authState, setAuthState] = useState({
		user: null,
		customer: null,
		tokens: null,
		isLoading: true,
	});

	useEffect(() => {
		const initializeAuth = () => {
			try {
				const storedTokens = storage.getItem("authTokens");
				const storedUser = storage.getItem("user");
				const storedCustomer = storage.getItem("customer");

				if (storedTokens && storedUser) {
					setAuthState({
						tokens: JSON.parse(storedTokens),
						user: JSON.parse(storedUser),
						customer: storedCustomer ? JSON.parse(storedCustomer) : null,
						isLoading: false,
					});
				} else {
					setAuthState((prev) => ({ ...prev, isLoading: false }));
				}
			} catch (error) {
				console.error("Error initializing auth:", error);
				setAuthState((prev) => ({ ...prev, isLoading: false }));
			}
		};

		initializeAuth();
	}, [storage]);

	const refreshAuth = async () => {
		if (!authState.tokens?.refreshToken) return;

		try {
			const { data } = await refreshTokenMutation({
				variables: { token: authState.tokens.refreshToken },
			});

			const { authToken, authTokenExpiration, success } = data.refreshToken;

			if (!success) {
				throw new Error("Failed to refresh token");
			}

			const newTokens = {
				authToken,
				refreshToken: authState.tokens.refreshToken,
				authTokenExpiration,
				refreshTokenExpiration: authState.tokens.refreshTokenExpiration,
			};

			storage.setItem("authTokens", JSON.stringify(newTokens));

			setAuthState((prev) => ({
				...prev,
				tokens: newTokens,
			}));
		} catch (error) {
			console.error("Error refreshing token:", error);
			logout();
		}
	};

	const login = async (username, password, onCartSync) => {
		try {
			console.log("🔐 Starting login process...");

			const { data } = await loginMutation({
				variables: { username, password },
			});

			const { user, customer, ...tokens } = data.login;

			console.log("✅ Login successful:", { user, customer });

			// Store auth data
			storage.setItem("authTokens", JSON.stringify(tokens));
			storage.setItem("user", JSON.stringify(user));
			if (customer) {
				storage.setItem("customer", JSON.stringify(customer));
			}

			// Update auth state
			setAuthState({
				user,
				customer,
				tokens,
				isLoading: false,
			});

			// Get customer ID for cart sync
			const customerId = customer?.databaseId || user?.databaseId;
			console.log("👤 Customer ID for cart sync:", customerId);

			// Sync cart if callback provided and customer ID exists
			if (onCartSync && customerId) {
				try {
					console.log("🛒 Syncing cart for logged in user...");
					await onCartSync(customerId);
					console.log("✅ Cart sync completed");
				} catch (cartError) {
					console.error("❌ Cart sync failed:", cartError);
					// Don't fail login if cart sync fails
				}
			}

			navigation.push("/my-account");
		} catch (error) {
			console.error("Error during login:", error);
			throw error;
		}
	};

	const logout = (onCartLogout) => {
		console.log("🔐 Logging out user...");

		// Clear auth storage
		storage.removeItem("authTokens");
		storage.removeItem("user");
		storage.removeItem("customer");
		// Clear WooCommerce session token as well
		if (typeof window !== "undefined") {
			localStorage.removeItem("woocommerce_session_token");
		}
		// Update auth state
		setAuthState({
			user: null,
			customer: null,
			tokens: null,
			isLoading: false,
		});

		// Handle cart logout if callback provided
		if (onCartLogout) {
			try {
				console.log("🛒 Handling cart logout...");
				onCartLogout();
				console.log("✅ Cart logout handled");
			} catch (cartError) {
				console.error("❌ Cart logout failed:", cartError);
			}
		}

		navigation.push("/");
	};

	return (
		<AuthContext.Provider
			value={{
				...authState,
				login,
				logout,
				refreshAuth,
				customerId: authState.customer?.databaseId || authState.user?.databaseId || null,
			}}
		>
			{children}
		</AuthContext.Provider>
	);
}

const useAuth = () => {
	const context = useContext(AuthContext);
	if (context === undefined) {
		throw new Error("useAuth must be used within an AuthProvider");
	}
	return context;
};

export { useAuth, AuthProvider };
