const TOKEN_KEY = "wp_jwt_token";

export function getToken() {
  if (typeof document !== "undefined") {
    const match = document.cookie.match(new RegExp("(^| )" + TOKEN_KEY + "=([^;]+)"));
    return match ? match[2] : null;
  }
  return null;
}

export function getTokenServerSide(cookieStore) {
  return cookieStore.get(TOKEN_KEY)?.value || null;
}

export function setToken(token) {
  if (typeof document !== "undefined") {
    document.cookie = `${TOKEN_KEY}=${token}; path=/;`;
  }
}

export function removeToken() {
  if (typeof document !== "undefined") {
    document.cookie = `${TOKEN_KEY}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
  }
}
