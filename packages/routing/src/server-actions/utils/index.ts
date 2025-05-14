import { cookies } from 'next/headers';
import { getWpUrl } from '../../lib/config';

/**
 * Sets the refresh token to a secure, HttpOnly cookie.
 * This can only be used within server actions and server routes.
 *
 * @param refreshToken The refresh token from the token endpoint
 * @param refreshTokenExpiration The refresh token expiration from the token endpoint in milliseconds
 */
export async function setRefreshToken(
  refreshToken: string,
  refreshTokenExpiration: number,
) {
  const cookieStore = cookies();
  const cookieName = `${getWpUrl()}-rt`;

  cookieStore.set(cookieName, refreshToken, {
    secure: process.env.NODE_ENV === 'production',
    httpOnly: true,
    path: '/',
    expires: new Date(refreshTokenExpiration),
    sameSite: 'lax',
  });
}

/**
 * Deletes the refresh token cookie.
 * This can only be used within server actions and server routes.
 *
 * @returns boolean True if the cookie was deleted, false if it didn't exist
 */
export async function deleteRefreshToken(): Promise<boolean> {
  const cookieStore = cookies();
  const wpCookieName = `${getWpUrl()}-rt`;
  const wpCookie = cookieStore.get(wpCookieName);

  if (wpCookie?.name) {
    cookieStore.delete(wpCookieName);
    return true;
  }
  
  return false;
}

/**
 * Gets the refresh token from the cookie.
 * This can only be used within server actions and server routes.
 *
 * @returns string|null The refresh token or null if not found
 */
export function getRefreshToken(): string | null {
  const cookieStore = cookies();
  const cookieName = `${getWpUrl()}-rt`;
  return cookieStore.get(cookieName)?.value || null;
}

/**
 * Check if an email address is valid
 *
 * @param email Email address to validate
 * @returns boolean True if the email is valid
 */
export function isValidEmail(email: string): boolean {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Check if a value is a string
 *
 * @param value Value to check
 * @returns boolean True if the value is a string
 */
export function isString(value: unknown): value is string {
  return typeof value === 'string' || value instanceof String;
}

