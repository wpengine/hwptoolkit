/**
 * Common type definitions for the routing package
 */

/**
 * Authentication response from WordPress
 */
export type AuthorizeResponse = {
  accessToken: string;
  accessTokenExpiration: number;
  refreshToken: string;
  refreshTokenExpiration: number;
};

/**
 * WordPress route match information
 */
export type RouteMatch = {
  pathname: string;
  params: Record<string, string>;
  query: Record<string, string>;
};

/**
 * Configuration options for the routing package
 */
export type RoutingConfig = {
  /**
   * WordPress URL
   */
  wpUrl: string;
  
  /**
   * WordPress secret key for authentication
   */
  secretKey: string;
  
  /**
   * GraphQL endpoint URL (defaults to /graphql if not specified)
   */
  graphqlEndpoint?: string;
};

