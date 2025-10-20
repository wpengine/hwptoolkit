export const WP_API_URL = process.env.NEXT_PUBLIC_WP_URL;

export async function fetchFromWordPress(endpoint: string, options?: RequestInit) {
  try {
    const response = await fetch(`${WP_API_URL}/wp-json${endpoint}`, {
      ...options,
      // Note: credentials: 'include' doesn't work across different localhost ports
      // For production, use Application Passwords or OAuth
    });

    if (!response.ok) {
      // Handle CORS errors (status 0 or null)
      if (response.status === 0 || !response.status) {
        throw new Error(
          `CORS error: WordPress is blocking cross-origin requests.\n\n` +
          `Possible causes:\n` +
          `• WordPress is not running (run: npx wp-env start)\n` +
          `• CORS plugin not active (check hwp-cors-local)\n` +
          `• Wrong WordPress URL in .env`
        );
      }

      // Try to parse WordPress error response
      let errorDetails = '';
      try {
        const errorData = await response.json();
        if (errorData.message) {
          errorDetails = errorData.message;
        }
        if (errorData.code) {
          errorDetails += ` (${errorData.code})`;
        }
      } catch {
        // Response body not JSON, use status text
        errorDetails = response.statusText;
      }

      // Handle specific status codes
      if (response.status === 401) {
        throw new Error(
          `Authentication required (401).\n\n` +
          `This endpoint requires authentication. ${errorDetails ? `\nDetails: ${errorDetails}` : ''}`
        );
      }

      if (response.status === 403) {
        throw new Error(
          `Permission denied (403).\n\n` +
          `You don't have permission to access this resource. ${errorDetails ? `\nDetails: ${errorDetails}` : ''}`
        );
      }

      if (response.status === 404) {
        const isRestRouteError = errorDetails.toLowerCase().includes('no route');
        throw new Error(
          `WordPress endpoint not found (404).\n\n` +
          (isRestRouteError
            ? `REST API routing error detected. This usually happens in wp-env when:\n` +
              `• hwp-wp-env-helpers plugin is not loaded\n` +
              `• Permalink structure conflicts with Docker\n\n` +
              `Try restarting wp-env: npx wp-env destroy && npx wp-env start`
            : `The endpoint ${endpoint} may not exist or WordPress needs configuration.`) +
          (errorDetails ? `\n\nDetails: ${errorDetails}` : '')
        );
      }

      if (response.status >= 500) {
        throw new Error(
          `WordPress server error (${response.status}).\n\n` +
          `Possible causes:\n` +
          `• WordPress fatal error or plugin conflict\n` +
          `• Database connection issues\n` +
          `• Check WordPress logs: npx wp-env run cli wp debug.log` +
          (errorDetails ? `\n\nDetails: ${errorDetails}` : '')
        );
      }

      // Generic error for other status codes
      throw new Error(
        `WordPress API error (${response.status})${errorDetails ? `\n\nDetails: ${errorDetails}` : ''}`
      );
    }

    return response.json();
  } catch (error) {
    // Network/connection errors
    if (error instanceof TypeError && error.message.includes('fetch')) {
      throw new Error(
        `Cannot connect to WordPress at ${WP_API_URL}\n\n` +
        `Possible causes:\n` +
        `• WordPress is not running (run: npx wp-env start)\n` +
        `• Wrong URL in .env file\n` +
        `• Network/firewall blocking connection\n\n` +
        `Check if WordPress is accessible: ${WP_API_URL}`
      );
    }
    throw error;
  }
}

export async function getCurrentUser() {
  // 🚨 WARNING: Demo-only code! 🚨
  // This function uses a hardcoded user ID (1), which is the default admin in wp-env.
  // DO NOT USE THIS PATTERN IN PRODUCTION. In production, use /wp/v2/users/me with proper authentication.
  if (process.env.NODE_ENV === 'production') {
    throw new Error(
      'getCurrentUser() uses a hardcoded user ID and MUST NOT be used in production. ' +
      'Use /wp/v2/users/me with Application Passwords or OAuth instead.'
    );
  }
  if (typeof window !== 'undefined' && window.console && window.console.warn) {
    window.console.warn(
      'WARNING: getCurrentUser() is using a hardcoded user ID (1). ' +
      'This is for demo purposes only and MUST NOT be used in production.'
    );
  }
  return fetchFromWordPress('/wp/v2/users/1');
}

export async function getPosts() {
  // Public endpoint - works without authentication
  return fetchFromWordPress('/wp/v2/posts?per_page=10');
}

export async function getPost(id: number) {
  // Public endpoint - works without authentication
  return fetchFromWordPress(`/wp/v2/posts/${id}`);
}
