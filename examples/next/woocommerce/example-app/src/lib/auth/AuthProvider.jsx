import React, { createContext, useContext, useEffect, useState } from "react";
import { useNextNavigation } from "../navigation";
import useLocalStorage from "../storage";
import { gql, useMutation } from "@apollo/client";

const LOGIN_MUTATION = gql`
  mutation loginWithPassword($username: String!, $password: String!) {
    login(
      input: {
        provider: PASSWORD
        credentials: { username: $username, password: $password }
      }
    ) {
      authToken
      authTokenExpiration
      refreshToken
      refreshTokenExpiration
      user {
        email
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

function AuthProvider({
  children,
  storage = useLocalStorage,
  navigation = useNextNavigation(),
}) {
  const [loginMutation] = useMutation(LOGIN_MUTATION);
  const [refreshTokenMutation] = useMutation(REFRESH_TOKEN_MUTATION);
  const [authState, setAuthState] = useState({
    user: null,
    tokens: null,
    isLoading: true,
  });

  useEffect(() => {
    const initializeAuth = () => {
      try {
        const storedTokens = storage.getItem("authTokens");
        const storedUser = storage.getItem("user");

        if (storedTokens && storedUser) {
          setAuthState({
            tokens: JSON.parse(storedTokens),
            user: JSON.parse(storedUser),
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

  const login = async (username, password) => {
    try {
      const { data } = await loginMutation({
        variables: { username, password },
      });

      const { user, ...tokens } = data.login;

      storage.setItem("authTokens", JSON.stringify(tokens));
      storage.setItem("user", JSON.stringify(user));

      setAuthState({
        user,
        tokens,
        isLoading: false,
      });

      navigation.push("/my-account");
    } catch (error) {
      console.error("Error during login:", error);
      throw error;
    }
  };

  const logout = () => {
    storage.removeItem("authTokens");
    storage.removeItem("user");

    setAuthState({
      user: null,
      tokens: null,
      isLoading: false,
    });

    navigation.push("/");
  };

  return (
    <AuthContext.Provider value={{ ...authState, login, logout, refreshAuth }}>
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
