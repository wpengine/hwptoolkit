export const WP_API_URL = process.env.NEXT_PUBLIC_WP_URL;

export async function fetchFromWordPress(endpoint: string, options?: RequestInit) {
  try {
    const response = await fetch(`${WP_API_URL}/wp-json${endpoint}`, {
      ...options,
      // Note: credentials: 'include' doesn't work across different localhost ports
      // For production, use Application Passwords or OAuth
    });

    if (!response.ok) {
      if (response.status === 0 || !response.status) {
        throw new Error('CORS error: Unable to connect to WordPress. Make sure WordPress is running and CORS is configured.');
      }
      throw new Error(`WordPress API error: ${response.status} ${response.statusText}`);
    }

    return response.json();
  } catch (error) {
    if (error instanceof TypeError && error.message.includes('fetch')) {
      throw new Error(`Cannot connect to WordPress at ${WP_API_URL}. Is wp-env running?`);
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
