import { cookies } from 'next/headers';
import { NextResponse } from 'next/server';
import { getWpUrl, getWpSecret } from '../../lib/config';
import { AuthorizeResponse } from '../../types';

/**
 * Handles authentication token requests to WordPress
 * 
 * @param req Request object from Next.js
 * @returns Response with authentication tokens or error
 */
export async function tokenHandler(req: Request) {
  try {
    const secretKey = getWpSecret();

    if (!secretKey) {
      throw new Error('WORDPRESS_SECRET_KEY must be set');
    }

    const { url } = req;
    const code = new URL(url).searchParams.get('code') ?? undefined;
    const cookieStore = cookies();
    const cookieName = `${getWpUrl()}-rt`;
    const refreshToken = cookieStore.get(cookieName)?.value;

    // Either code or refresh token must be provided
    if (!refreshToken && !code) {
      return new Response(JSON.stringify({ error: 'Unauthorized', message: 'No code or refresh token provided' }), {
        status: 401,
        headers: {
          'Content-Type': 'application/json',
        },
      });
    }

    const wpAuthorizeEndpoint = `${getWpUrl()}/?rest_route=/faustwp/v1/authorize`;

    const response = await fetch(wpAuthorizeEndpoint, {
      headers: {
        'Content-Type': 'application/json',
        'x-faustwp-secret': secretKey,
      },
      method: 'POST',
      body: JSON.stringify({
        code,
        refreshToken,
      }),
    });

    if (!response.ok) {
      // If authentication fails, delete the refresh token cookie
      // This handles cases where the token is expired, invalid, or revoked
      if (refreshToken) {
        cookieStore.delete(cookieName);
      }

      const errorResponse = await response.json().catch(() => ({ message: 'Invalid response from WordPress' }));
      
      return new Response(JSON.stringify({ 
        error: 'Unauthorized', 
        message: errorResponse.message || 'Authentication failed'
      }), {
        status: 401,
        headers: {
          'Content-Type': 'application/json',
        },
      });
    }

    const data = (await response.json()) as AuthorizeResponse;

    // Create response with new tokens
    const res = NextResponse.json(data, { status: 200 });

    // Set the refresh token as a secure, HttpOnly cookie
    res.cookies.set(cookieName, data.refreshToken, {
      secure: process.env.NODE_ENV === 'production',
      httpOnly: true,
      path: '/',
      expires: new Date(data.refreshTokenExpiration * 1000),
      sameSite: 'lax',
    });

    return res;
  } catch (err) {
    console.error('Error in authorize handler:', err);

    return new Response(JSON.stringify({ 
      error: 'Internal Server Error',
      message: err instanceof Error ? err.message : 'Unknown error'
    }), {
      status: 500,
      headers: {
        'Content-Type': 'application/json',
      },
    });
  }
}

