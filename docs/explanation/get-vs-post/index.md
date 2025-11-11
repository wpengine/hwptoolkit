---
title: "GET vs POST in WPGraphQL"
description: "A guide on the differences between using a GET request with a query parameter versus a POST request to the /graphql endpoint."
---

## Overview

When interacting with WPGraphQL, selecting the correct HTTP method to fetch data is crucial. This guide explains the differences between using a GET request with a query parameter versus a POST request to the /graphql endpoint.

## Context

WPGraphQL is a GraphQL API for WordPress, enabling flexible and efficient data queries. Unlike traditional REST APIs that require multiple endpoints, GraphQL allows querying specific data through a single endpoint.

# Details

WPGraphQL supports querying data using [the endpoint](/docs/explanation/graphql-endpoints.md) in two primary ways:

* **GET Request with Query Parameter**: You can query WPGraphQL by appending your GraphQL query as a query parameter to the `/graphql` endpoint. This method is useful for simple queries or testing purposes.

```bash
curl 'http://myexample.local/graphql?query={generalSettings{url}}'
```
> [!IMPORTANT]
GET requests have inherent URL length limitations (typically 2,000-8,000 characters depending on the browser and server). Complex GraphQL queries can easily exceed these limits, making this method impractical for anything beyond basic queries.

Each property is provided as an HTTP query parameter, with values separated by an ampersand (&).

GraphQL requests with variables can work in GET requests, but the variables parameter must be properly encoded as a JSON string:

```text
http://myexample.local/graphql?query=query($slug:ID!){post(id:$slug,idType:SLUG){title,content}}&variables={%22slug%22:%22hello-world%22}
```

Only query operations can be executed; mutation operations don't work with GET requests.

GET requests can be particularly beneficial when used with caching mechanisms like the [Smart Cache plugin](https://wordpress.org/plugins/wpgraphql-smart-cache/). Since GET queries are part of the URL, they can be easily cached, reducing server load and improving response times for frequently requested data. This makes them a good choice for read-only queries where performance and efficiency are priorities.

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
| GET with Query Parameter | URLs in GET requests are visible in browser history, logs, and referrer headers, but when using HTTPS, the query itself is encrypted in transit. | Simple queries (no mutations)  | Testing    | simple data retrieval |

# Summary
While both methods have their uses, POST requests are generally recommended for WPGraphQL due to their flexibility, security advantages, and ability to handle complex queries. However, GET requests can be useful for simple, cacheable queries and is useful when paired with caching mechanisms like the Smart Cache plugin. Consider your specific use case, security requirements, and caching needs when choosing between the two methods.

## Contributing

If you feel like something is missing or you want to add documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.
