import { RoutingConfig } from '../types';

// Default configuration
let config: RoutingConfig = {
  wpUrl: process.env.WORDPRESS_URL || '',
  secretKey: process.env.WORDPRESS_SECRET_KEY || '',
  graphqlEndpoint: '/graphql',
};

/**
 * Set configuration for the routing package
 */
export function setConfig(newConfig: Partial<RoutingConfig>): void {
  config = {
    ...config,
    ...newConfig,
  };
}

/**
 * Get the full configuration
 */
export function getConfig(): RoutingConfig {
  return config;
}

/**
 * Get the WordPress URL
 */
export function getWpUrl(): string {
  return config.wpUrl;
}

/**
 * Get the WordPress secret key
 */
export function getWpSecret(): string {
  return config.secretKey;
}

/**
 * Get the GraphQL endpoint URL
 */
export function getGraphqlEndpoint(): string {
  const wpUrl = getWpUrl();
  const endpoint = config.graphqlEndpoint;
  
  // If wpUrl ends with a slash and endpoint starts with a slash,
  // we need to avoid double slashes
  if (wpUrl.endsWith('/') && endpoint.startsWith('/')) {
    return `${wpUrl}${endpoint.substring(1)}`;
  }
  
  // If wpUrl doesn't end with a slash and endpoint doesn't start with a slash,
  // we need to add a slash
  if (!wpUrl.endsWith('/') && !endpoint.startsWith('/')) {
    return `${wpUrl}/${endpoint}`;
  }
  
  return `${wpUrl}${endpoint}`;
}

