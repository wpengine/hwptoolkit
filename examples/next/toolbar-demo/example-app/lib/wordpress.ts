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
  // Demo: Using user ID 1 (wp-env default admin) for simplicity
  // Production: Use /wp/v2/users/me with Application Passwords or OAuth
  // Note: This is acceptable in demos where auth setup would add unnecessary complexity
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
