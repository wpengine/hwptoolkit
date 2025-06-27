"use client";

import { createContext, useContext, useEffect, useState } from "react";
import { getToken, removeToken, setToken } from "./authUtils";

const WP_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

const AuthContext = createContext(undefined);

function AuthProvider({ children }) {
  const [authState, setAuthState] = useState({
    isLogged: false,
    isLoading: true,
    error: null,
  });

  useEffect(() => {
    const initializeAuth = async () => {
      const token = getToken();
      if (!token) {
        setAuthState((prev) => ({ ...prev, isLoading: false }));
        return;
      }

      // Validate token
      try {
        const validateRes = await fetch(`${WP_URL}/wp-json/jwt-auth/v1/token/validate`, {
          method: "POST",
          headers: { Authorization: `Bearer ${token}` },
        });

        if (!validateRes.ok) throw new Error("Invalid token");

        setAuthState({
          isLogged: true,
          isLoading: false,
          error: null,
        });
      } catch {
        removeToken();
        setAuthState({
          isLogged: false,
          isLoading: false,
          error: null,
        });
      }
    };
    initializeAuth();
  }, []);

  const login = async (username, password) => {
    setAuthState((prev) => ({ ...prev, isLoading: true, error: null }));

    try {
      const res = await fetch(`${WP_URL}/wp-json/jwt-auth/v1/token`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, password }),
      });
      const data = await res.json();

      if (res.ok && data.token) {
        setToken(data.token);

        setAuthState({
          isLogged: true,
          isLoading: false,
          error: null,
        });

        return { success: true };
      } else {
        setAuthState({
          isLogged: false,
          isLoading: false,
          error: data.message || "Login failed",
        });

        throw new Error(data.message || "Login failed");
      }
    } catch (error) {
      setAuthState({
        isLogged: false,
        isLoading: false,
        error: error.message || "Network error",
      });
      throw error;
    }
  };

  const logout = async () => {
    removeToken();
    setAuthState({
      isLogged: false,
      isLoading: false,
      error: null,
    });
  };

  return <AuthContext.Provider value={{ ...authState, login, logout }}>{children}</AuthContext.Provider>;
}

const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
};

export { AuthProvider, useAuth };
