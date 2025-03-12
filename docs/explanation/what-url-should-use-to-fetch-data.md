# What URL should I use to fetch data from WPGraphQL? (/graphql with query parameter vs /graphql with POST request)

When interacting with WPGraphQL, choosing the correct URL and method to fetch data is crucial. This explanation clarifies the difference between using a GET request with a query parameter and a POST request to the `/graphql` endpoint.

## Context

WPGraphQL is a GraphQL API for WordPress, allowing you to query data in a flexible and efficient manner. GraphQL itself is a query language for APIs that provides a unified endpoint for data retrieval. Historically, APIs have used REST endpoints, which require multiple requests to gather related data. GraphQL, on the other hand, allows you to fetch specific data with a single query. WPGraphQL integrates this capability into WordPress.

# Details

WPGraphQL supports querying data using the `/graphql` endpoint in two primary ways:

* **GET Request with Query Parameter**: You can query WPGraphQL by appending your GraphQL query as a query parameter to the `/graphql` endpoint. This method is useful for simple queries or testing purposes.

```bash
curl 'http://myexample.local/graphql?query={generalSettings{url}}'
```
**Note**: GET requests have inherent URL length limitations (typically 2,000-8,000 characters depending on the browser and server). Complex GraphQL queries can easily exceed these limits, making this method impractical for anything beyond basic queries.

* **POST Request**: This is the standard method for querying WPGraphQL. You send a POST request to the `/graphql` endpoint with your GraphQL query in the request body. It supports complex queries and is more secure.

```bash
curl -X POST \
  http://myexample.local/graphql \
  -H 'Content-Type: application/json' \
  -d '{"query": "{ generalSettings { url } }"}'
```

# Comparison
| Method                   | Security                                 | Complexity      | Support    | Use Case               |
|--------------------------|------------------------------------------|-----------------|------------|------------------------|
| POST                     | More secure, hides query in request body | Complex queries | Production | complex data retrieval  |
| GET with Query Parameter | Less secure due to query exposure in URL | Simple queries  | Testing    | simple data retrieval |
