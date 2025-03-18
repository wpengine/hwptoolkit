# Which WPGraphQL endpoints to use: /graphql vs ?graphql

WPGraphQL exposes a GraphQL endpoint that developers can use to interact with the WordPress backend and retrieve data.

The plugin provides two primary ways to interact with this API:

1. The pretty URL endpoint `/graphql`
2. The query-string-based endpoint `/index.php?graphql`

Both serve the same purpose of providing access to the GraphQL API, but they function slightly differently.

## The default `/graphql` endpoint

The `/graphql` default endpoint is the preferred choice for most users because it is easy to read, share, and remember.

WPGraphQL allows you to customize the `/graphql` endpoint using a filter. If you'd like to change the endpoint to something more specific to your site, you can do so with a simple code snippet.

```php
function my_new_graphql_endpoint() {
  return 'my_endpoint';
};

add_filter( 'graphql_endpoint', 'my_new_graphql_endpoint' );
```
This code would change the default `/graphql` endpoint to `/my_endpoint`.

## The query string endpoint: `/index.php?graphql`

The `/index.php?graphql` endpoint is the preferred choice for those looking for stability and compatibility.

This query-string endpoint serves as a fallback for WordPress sites that do not have pretty permalinks enabled. If a site is running with the default URL structure (e.g., `example.com/?p=123`), the `/graphql` pretty URL might not work. In this case, `/index.php?graphql` can be used to access the GraphQL API.

## Which one should you use and why?

If your application only talks to a WP instance(s) with pretty permalinks enabled and you trust that will not change, it’s safe to use the clean `/graphql` endpoint.

If you don't know or don't trust the config to change, `/index.php?graphql` is recommended. WordPress is very flexible with URL routing, and even if your permalinks are enabled, the URL `/index.php?graphql` is still valid.