// Re-export server module types for consumers
export type { AuthorizeResponse, RouteMatch, RoutingConfig } from './types';

// Export config functions
export { setConfig, getConfig, getWpUrl, getWpSecret, getGraphqlEndpoint } from './lib/config';

