import { notFound } from 'next/navigation';
import { tokenHandler } from './tokenHandler';

/**
 * Handles GET requests to the Faust API routes
 * 
 * @param req Request object from Next.js
 * @returns Response based on the requested route
 */
export async function GetFn(req: Request) {
  const { pathname } = new URL(req.url);

  // Route mapping for API endpoints
  switch (pathname) {
    case '/api/hwp/token/':
    case '/api/hwp/token': {
      return tokenHandler(req);
    }
    default: {
      return notFound();
    }
  }
}

/**
 * Handles POST requests to the Faust API routes
 * 
 * @param req Request object from Next.js
 * @returns Response based on the requested route
 */
export async function PostFn(req: Request) {
  const { pathname } = new URL(req.url);

  // Route mapping for API endpoints
  switch (pathname) {
    // Add POST endpoints here if needed
    default: {
      return notFound();
    }
  }
}

/**
 * Route handler for use in Next.js App Router
 */
export const routeHandler = {
  GET: (req: Request) => GetFn(req),
  POST: (req: Request) => PostFn(req),
};

/**
 * Creates a route handler for use in Next.js App Router
 * For use with app/api/hwp/[[...slug]]/route.ts
 * 
 * @example
 * ```ts
 * // app/api/hwp/[[...slug]]/route.ts
 * import { createRouteHandler } from '@wpe/hwptoolkit-routing/server';
 * 
 * export const { GET, POST } = createRouteHandler();
 * ```
 */
export function createRouteHandler() {
  return {
    GET: routeHandler.GET,
    POST: routeHandler.POST,
  };
}

