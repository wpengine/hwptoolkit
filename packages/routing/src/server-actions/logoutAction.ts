'use server';

import { deleteRefreshToken } from './utils';

/**
 * Server action to handle user logout
 * Removes the refresh token cookie
 * 
 * @returns boolean True if a token was found and deleted, false otherwise
 */
export async function onLogout(): Promise<boolean> {
  return deleteRefreshToken();
}

