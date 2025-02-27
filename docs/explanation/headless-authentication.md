# What is Headless Authentication?

Headless authentication refers to the process of verifying user identity and managing access control in a decoupled architecture where the content management system (WordPress) and the user-facing frontend are separate systems. Unlike traditional WordPress sites where authentication happens within a single application, headless authentication must work across systems, that may be located accross different regions.

## Why authentication changes in Headless Architectures

In a traditional WordPress setup, authentication follows a straightforward flow:

1. Users log in through WordPress's login form
2. WordPress sets authentication cookies in the user's browser
3. These cookies are automatically included with each subsequent request
4. WordPress validates these cookies on each request to identify the user

This works because the interactions happen within the same domain and application context. The browser's same-origin policy ensures cookies set by WordPress are automatically included in future requests to WordPress.

However, in a headless architecture:

* WordPress serves as a content API at one domain (e.g., cms.example.com)
* The frontend application lives at another domain (e.g., example.com)
* Browser security mechanisms prevent cookies from automatically traveling between these domains (CORS)
* API requests need explicit authentication credentials with each request

This fundamental change requires completely different authentication strategies. 

## Major Authentication Approaches for Headless WordPress
Here are the following options regarding authentication from remote systems:

1. **Application Passwords**
WordPress 5.6 introduced Application Passwords as a native authentication method for API access. Here are some of its features:

* Users generate application-specific passwords through their WordPress profile
* These passwords are used exclusively for API authentication, not for WordPress login
* Each application can have its own password that can be individually revoked
* API requests use Basic Authentication with these credentials. This means that the requests must be in encrypted form (HTTPS)

Application Passwords is the only build-in method so far that allows external systems to request authenticated data via the REST API.

2. **Token-Based Authentication (JWT)**
JSON Web Tokens (JWT) have become the most popular solution for headless WordPress authentication:

* When users authenticate, they receive a signed token containing their identity and permissions
* This token is stored by the frontend application (in storage or cookies)
* The token is included with API requests to WordPress
* WordPress validates the token's signature to verify the user's identity

JWT tokens are stateless, meaning WordPress doesn't need to store session information—all necessary data is contained within the token itself.

Notable plugins that support JWT are: [wp-graphql-jwt-authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication), [wp-graphql-headless-login](https://github.com/AxeWP/wp-graphql-headless-login/tree/main) and [JWT Auth](https://wordpress.org/plugins/jwt-auth/)

3. **Basic Credentials Auth**
Basic Credentials Authentication (often called "Basic Auth") is one of the simplest authentication methods available for headless WordPress implementations. It involves sending a username and password with each API request to verify the user's identity. It is primarily intended for development/testing.


With all the above options available, we want to understand the security caveats when building web applications that authenticate with WordPress in some way.

## Security Considerations
When implementing authentication in a headless WordPress setup, several security considerations deserve special attention:

### Token Storage
How and where you store authentication tokens significantly impacts security. Storing tokens in `localStorage` could make them vulnerable to XSS attacks, while HTTP-only cookies provide better protection but require additional CORS configuration.

### CORS (Cross-Origin Resource Sharing)
Since your frontend and WordPress backend operate on different domains, proper CORS configuration is essential. You'll need to configure WordPress to accept requests from your frontend domain while still maintaining security boundaries.

### Token Expiration and Refresh Strategies
Implementing token expiration reduces the window of opportunity for token misuse, but requires strategies for refreshing tokens without disrupting the user experience. A common pattern involves:

1. Using short-lived access tokens for API requests
2. Maintaining longer-lived refresh tokens to obtain new access tokens
3. Implementing silent refresh mechanisms that work in the background

Obviously a lot of the above patterns can be abstracted and supported by dedicated client side authentication frameworks like [next-auth](https://next-auth.js.org/) or [better auth](https://www.better-auth.com/). The key point of contention resides with how these frameworks interact with WordPress's authentication system and whether they can properly handle the nuances of WordPress's permission model.

Frameworks like Next-Auth provide excellent abstractions for social logins, JWT handling, and session management, but often require custom adapters or additional configuration to properly sync with WordPress's authentication expectations.

Therefore when implementing an authentication framework with headless WordPress, **developers need to carefully consider these points of contention and potentially create custom adapters or middleware that bridge the gap between the framework's authentication model and WordPress's expectations**.

## Our recommended authentication approach
Based on the above, we recommend the following solutions.

### Server-Side: Dedicated headless authentication plugin
We suggest using a dedicated headless authentication plugin like **WPGraphQL Headless Login** that specifically caters to the needs of decoupled WordPress architectures. This dedicated plugin ensures that your server-side authentication is robust and properly integrated with WordPress's security model, while offering the flexibility needed for headless implementations.

### Client-Side: Framework-agnostic authentication libraries
On the client side, we recommend using thin wrappers or extensions built on top of established authentication frameworks like Next-Auth, or Auth.js. These thin wrappers should handle WordPress-specific authentication details while delegating most functionality to the underlying authentication framework. This makes it easier to:

1. Swap frontend frameworks if needed
2. Update authentication libraries independently
3. Maintain clear authentication flows

## Comprehensive tutorials and how to guides
To tie everything together, we will provide:

* Step-by-step tutorial covering the complete authentication implementation using basic credentials
* Framework-specific examples showing real-world implementations
* Security best practices specific to headless WordPress
* Troubleshooting guides for common authentication issues

Our approach to authentication in headless WordPress emphasizes **modularity**, **security**, and developer **experience**. To support these principles, we will provide **ready-to-use code snippets** and **example boilerplate code** that you can easily integrate into your application.

## Example use case
For example, if you're building a headless WordPress application with a React frontend, you can use our code snippets to implement Credentials authentication. You can integrate our boilerplate code for handling access tokens securely, including token storage and refresh logic, without needing to install additional dependencies.