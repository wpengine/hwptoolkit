import React, { createContext, useContext, useEffect, useState, useCallback, useRef, useMemo } from "react";
import { useMutation } from "@apollo/client";
import { useRouter } from "next/router";
import useLocalStorage from "../storage";
import { LOGIN_MUTATION, REFRESH_TOKEN_MUTATION } from "@/lib/graphQL/userGraphQL";

const AuthContext = createContext(undefined);

export function AuthProvider({ children }) {
	const router = useRouter();
	const storage = useLocalStorage;

	const [authState, setAuthState] = useState({
		user: null,
		tokens: null,
		isLoading: true,
	});

	const refreshIntervalRef = useRef(null);

	const [loginMutation] = useMutation(LOGIN_MUTATION);
	const [refreshTokenMutation] = useMutation(REFRESH_TOKEN_MUTATION);

	// Login Function
	const login = useCallback(async (username, password) => {
		try {
			const { data } = await loginMutation({
				variables: { username, password },
			});

			if (!data?.login) {
				throw new Error("Login failed - no data returned");
			}

			const { user, customer, ...tokens } = data.login;

			storage.saveToLocalStorage("authTokens", tokens);
			storage.saveToLocalStorage("user", user);
			storage.saveToLocalStorage("sessionToken", customer.sessionToken);
			setAuthState({
				user,
				tokens,
				isLoading: false,
			});

			//router.push("/my-account");
		} catch (error) {
			console.error("❌ Login error:", error);
			throw error;
		}
	}, []);

	// Logout function
	const logout = useCallback(() => {
		if (refreshIntervalRef.current) {
			clearInterval(refreshIntervalRef.current);
			refreshIntervalRef.current = null;
		}

		storage.removeItem("user");
		storage.removeItem("authTokens");

		setAuthState({
			user: null,
			tokens: null,
			isLoading: false,
		});

		router.push("/");
	}, [router]);

	// Check if token should be refreshed (less than 2 minutes remaining)
	const shouldRefreshToken = useCallback((tokens) => {
		if (!tokens?.authTokenExpiration) return true;

		const expirationTime = tokens.authTokenExpiration * 1000;
		const currentTime = new Date().getTime();
		const timeUntilExpiration = expirationTime - currentTime;

		// Refresh if less than 2 minutes remaining
		return timeUntilExpiration < 2 * 60 * 1000;
	}, []);

	// Refresh Auth Token - internal function, not exposed
	const refreshAuth = useCallback(async () => {
		const currentTokens = storage.getItem("authTokens") ? JSON.parse(storage.getItem("authTokens")) : null;

		if (!currentTokens?.refreshToken) {
			return false;
		}

		try {
			const { data } = await refreshTokenMutation({
				variables: { token: currentTokens.refreshToken },
			});

			const { authToken, authTokenExpiration, success } = data.refreshToken;

			if (!success) {
				throw new Error("Failed to refresh token");
			}

			const newTokens = {
				authToken,
				refreshToken: currentTokens.refreshToken,
				authTokenExpiration,
				refreshTokenExpiration: currentTokens.refreshTokenExpiration,
			};

			storage.setItem("authTokens", JSON.stringify(newTokens));

			setAuthState((prev) => ({
				...prev,
				tokens: newTokens,
			}));

			return true;
		} catch (error) {
			console.error("❌ Error refreshing token:", error);
			logout();
			return false;
		}
	}, [refreshTokenMutation, logout]);

	// Initialize Auth on mount
	useEffect(() => {
		const storedTokens = storage.getFromLocalStorage("authTokens");
		const storedUser = storage.getFromLocalStorage("user");

		if (storedTokens && storedUser) {
			setAuthState({
				user: storedUser,
				tokens: storedTokens,
				isLoading: false,
			});
		} else {
			setAuthState({ user: null, tokens: null, isLoading: false });
		}
	}, []);

	// Setup auto-refresh
	useEffect(() => {
		if (refreshIntervalRef.current) {
			clearInterval(refreshIntervalRef.current);
		}

		if (authState.user && authState.tokens?.refreshToken) {
			const checkAndRefresh = () => {
				const tokens = storage.getItem("authTokens") ? JSON.parse(storage.getItem("authTokens")) : null;

				if (tokens && shouldRefreshToken(tokens)) {
					refreshAuth();
				}
			};

			checkAndRefresh();
			refreshIntervalRef.current = setInterval(checkAndRefresh, 60000);
		}

		return () => {
			if (refreshIntervalRef.current) {
				clearInterval(refreshIntervalRef.current);
			}
		};
	}, [authState.user, authState.tokens?.refreshToken]);

	const value = useMemo(
		() => ({
			user: authState.user,
			tokens: authState.tokens,
			isLoading: authState.isLoading,
			refreshAuth,
			logout,
			login,
		}),
		[authState.user, authState.isLoading, authState.tokens, login, logout, refreshAuth]
	);

	return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export const useAuth = () => {
	const context = useContext(AuthContext);
	if (!context) throw new Error("useAuth must be used within an AuthProvider");

	return useMemo(
		() => ({
			user: context.user,
			isLoading: context.isLoading,
			login: context.login,
			logout: context.logout,
		}),
		[context.user, context.isLoading, context.login, context.logout]
	);
};

export const useAuthAdmin = () => {
	const context = useContext(AuthContext);
	if (!context) throw new Error("useAuthAdmin must be used within an AuthProvider");

	return {
		...useAuth(),
		login: context.login,
		logout: context.logout,
		tokens: context.tokens,
		refreshAuth: context.refreshAuth,
	};
};
