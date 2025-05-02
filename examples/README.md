# HWP Toolkit Examples
This directory contains examples demonstrating how to use various features of the Headless WordPress Toolkit.

> **Feature Highlight:** Most examples include a `wp-env` setup, allowing you to fully configure your headless application with a single command. They also contain screenshots of the application.

## Astro Example

| Title                                                                                     | Description                                                                                     |
|-------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|
| [template-hierarchy-data-fetching-urql](astro/template-hierarchy-data-fetching-urql) | Demonstrates template hierarchy and data fetching with Astro, URQL, and Headless WordPress.    |

## Next.js Examples

| Title                                                                                     | Description                                                                                     |
|-------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------|
| [apollo-authentication](next/apollo-authentication)                                       | Showcases authentication with Next.js, Apollo Client, and Headless WordPress.                  |
| [apollo-client-data-fetch](next/apollo-client-data-fetch)                                 | Explores data fetching strategies and state management using Next.js, Apollo Client, and Headless WordPress. |
| [apollo-client-filesystem-routing](next/apollo-client-filesystem-routing)                 | Integrates WPGraphQL and WPGraphQL for ACF with Next.js for a headless WordPress site.         |
| [client-app-router-fetch-data](next/client-app-router-fetch-data)                         | Uses Next.js App Router and fetch API to retrieve data from WordPress via WPGraphQL.           |
| [client-multisite-app-router-fetch-data](next/client-multisite-app-router-fetch-data)     | Implements a multisite headless WordPress app with Next.js App Router and fetch API.           |
| [custom-sitemap-apollo](next/custom-sitemap-apollo)                                       | Generates a custom sitemap using Next.js, Apollo Client, and WPGraphQL with an extended plugin.|
| [custom-sitemap-vanilla-wpgraphql](next/custom-sitemap-vanilla-wpgraphql)                 | Creates a custom sitemap using Next.js and WPGraphQL without extending its endpoints.          |
| [hybrid-sitemap-apollo](next/hybrid-sitemap-apollo)                                       | Fetches and transforms WordPress sitemaps for clean URL formatting with Next.js.               |
| [proxied-sitemap-apollo](next/proxied-sitemap-apollo)                                     | Provides a proxied sitemap by transforming WordPress XML sitemaps for SEO-friendly frontend URLs.|
| [render-blocks-pages-router](next/render-blocks-pages-router)                             | Renders WordPress Blocks with JSX in Next.js, including utilities for hierarchical block data.  |
| [wp-theme-rendered-blocks](next/wp-theme-rendered-blocks)                             | Demonstrates how to fetch and apply WordPress Global Styles in a Next.js project using the `globalStylesheet` GraphQL field.  |

## Contributing

If you feel like something is missing or you want to add an example for another framework, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.

## Screenshots

> **Feature Highlight:** Here are some sample screenshots of the examples listed above.

### Apollo Authentication

<details>
    <summary>View Screenshots</summary>

![Enable Credentials Authentication](next/apollo-authentication/screenshots/enable-credentials-auth.png)

![Logged In View](next/apollo-authentication/screenshots/logged.png)

![Login Screen](next/apollo-authentication/screenshots/login.png)

</details>

### Apollo Client Data Fetch

<details>
    <summary>View Screenshots</summary>

![Homepage View](next/apollo-client-data-fetch/screenshots/home.png)
![Live Search Feature](next/apollo-client-data-fetch/screenshots/live-search.png)
![Load More Button](next/apollo-client-data-fetch/screenshots/load-more.png)
![New Comment Form](next/apollo-client-data-fetch/screenshots/new-comment.png)
![Post with Comments](next/apollo-client-data-fetch/screenshots/post-with-comments.png)
![Static Page Example](next/apollo-client-data-fetch/screenshots/static-page.png)

</details>

### Apollo Client Filesystem Routing

<details>
    <summary>View Screenshots</summary>

![Categories View](next/apollo-client-filesystem-routing/screenshots/categories.png)
![Category Page](next/apollo-client-filesystem-routing/screenshots/category.png)
![Homepage View](next/apollo-client-filesystem-routing/screenshots/home.png)
![Posts Overview](next/apollo-client-filesystem-routing/screenshots/posts.png)
![Single CPT Example](next/apollo-client-filesystem-routing/screenshots/single-cpt.png)
![Single Post View](next/apollo-client-filesystem-routing/screenshots/single-post.png)

</details>

### Client App Router Fetch Data

<details>
    <summary>View Screenshots</summary>

![Blog Comment Form Submitted](next/client-app-router-fetch-data/screenshots/blog-comment-form-submitted.png)
![Blog Comment Form](next/client-app-router-fetch-data/screenshots/blog-comment-form.png)
![Blog Comments](next/client-app-router-fetch-data/screenshots/blog-comments.png)
![Blog Listing Pagination](next/client-app-router-fetch-data/screenshots/blog-listing-pagination.png)
![Blog Listing](next/client-app-router-fetch-data/screenshots/blog-listing.png)
![Blog Single](next/client-app-router-fetch-data/screenshots/blog-single.png)
![CPT Event Listing](next/client-app-router-fetch-data/screenshots/cpt-event-listing.png)
![CPT Event Single](next/client-app-router-fetch-data/screenshots/cpt-event-single.png)

</details>

### Client Multisite App Router Fetch Data

<details>
    <summary>View Screenshots</summary>

![Blog Listing Pagination](next/client-multisite-app-router-fetch-data/screenshots/Blog_listing_pagination.png)
![Blog Listing](next/client-multisite-app-router-fetch-data/screenshots/Blog_listing.png)
![Catch All Second Site](next/client-multisite-app-router-fetch-data/screenshots/Catch_all_second_site.png)
![Catch All](next/client-multisite-app-router-fetch-data/screenshots/Catch_all.png)
![Comment Form](next/client-multisite-app-router-fetch-data/screenshots/Comment_form.png)
![Comments](next/client-multisite-app-router-fetch-data/screenshots/Comments.png)
![CPT Single](next/client-multisite-app-router-fetch-data/screenshots/Cpt_single.png)
![CPT](next/client-multisite-app-router-fetch-data/screenshots/cpt.png)
![Home](next/client-multisite-app-router-fetch-data/screenshots/Home.png)
![Single Blog](next/client-multisite-app-router-fetch-data/screenshots/Single_blog.png)

</details>

### Custom Sitemap Apollo

<details>
    <summary>View Screenshots</summary>

![Sitemap Category](next/custom-sitemap-apollo/screenshots/sitemap-category.png)
![Sitemap CPT](next/custom-sitemap-apollo/screenshots/sitemap-cpt.png)
![Sitemap CTT](next/custom-sitemap-apollo/screenshots/sitemap-ctt.png)
![Sitemap Index](next/custom-sitemap-apollo/screenshots/sitemap-index.png)
![Sitemap Page](next/custom-sitemap-apollo/screenshots/sitemap-page.png)
![Sitemap Post](next/custom-sitemap-apollo/screenshots/sitemap-post.png)
![Sitemap Tag](next/custom-sitemap-apollo/screenshots/sitemap-tag.png)
![Sitemap User](next/custom-sitemap-apollo/screenshots/sitemap-user.png)

</details>


### Custom Sitemap Vanilla WPGraphQL

<details>
    <summary>View Screenshots</summary>

![Sitemap Category](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-category.png)

![Sitemap CPT](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-cpt.png)

![Sitemap Index](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-index.png)

![Sitemap Page](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-page.png)

![Sitemap Post](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-post.png)

![Sitemap Tag](next/custom-sitemap-vanilla-wpgraphql/screenshots/sitemap-tag.png)

</details>
