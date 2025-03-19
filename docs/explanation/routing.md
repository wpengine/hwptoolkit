# Routing in Headless WordPress: A Comprehensive Guide

This guide explores the intricacies of implementing routing in a headless WordPress setup, focusing on handling regular posts and pages. We'll cover the core challenges, framework-specific implementations, template resolution strategies, and advanced considerations for optimizing your headless WordPress site.

## Understanding native WordPress routing mechanism
WordPress determines the content to display based on URL structure, using `wp_rewrite` and query parameters. The core routing rules rely on:

* Pretty Permalinks (e.g., `/blog/my-post/`)

* Query String-Based Routing (e.g., `/index.php?post_type=post&p=123`)

* Rewrite Rules (e.g., `/category/news/` maps to `index.php?category_name=news`)

## How custom post types impact routing
In traditional WordPress, custom post types (CPTs) are registered using `register_post_type()` and automatically get rewrite rules based on their settings. For example, a CPT called `portfolio` with the `has_archive` option enabled might have URLs like:

* `/portfolio/` → Archive page for portfolio items
* `/portfolio/project-name/` → Single portfolio item

## Implementing Routing in a Headless WordPress Setup

Unlike traditional WordPress, a headless setup requires the frontend to manually interpret WordPress URLs and dynamically determine the associated content type. Once identified, the correct template must be matched to the content type while handling query parameters and custom post types. This process is not trivial, as it requires executing specific GraphQL queries in a structured manner to retrieve the necessary data for accurate rendering.

### Dynamic Content Handling
In a headless WordPress setup, managing dynamic content becomes more complex as the frontend is decoupled from the backend.

Problem: WordPress generates dynamic URLs for posts, pages, and archives that don't automatically map to frontend routes.

Solution: Implement a flexible routing system on the frontend that can interpret WordPress URLs. Here is an example using Next.js pages router and a catch all route:

```javascript
// Next.js dynamic route
// pages/[...slug].js
export async function getServerSideProps({ params }) {
  const { slug } = params;
  const content = await fetchPageContent(slug.join('/'));
  return { props: { content } };
}
```
This function, `getServerSideProps`, is a Next.js pages router server-side data-fetching function. It allows you to fetch and render content dynamically for any URL path, such as `/about/team` or `/blog/post-title`. The fetched data (content) is then passed to the page component as a prop for rendering.

## Template Hierarchy Replication
The WordPress template hierarchy is a powerful feature that determines which template file to use for rendering content. Replicating this in a headless setup requires a different approach.

In traditional WordPress, the framework uses the query string to decide which template or set of templates should be used to display the page. If WordPress cannot find a template file with a matching name, it will skip to the next file in the hierarchy. If WordPress cannot find any matching template file, the theme’s `index.php` file will be used.

```text
single-post.php → single.php → singular.php → index.php
```

In headless WordPress we can use GraphQL queries to fetch template information. Then we can chose to simulate the template hierarchy resolution logic to determine the appropriate component to render. **Thus the key difference is athe shift from file-based to data-driven template selection.**

## Framework-Specific Implementations
Different frontend frameworks offer various approaches to handling routing in a headless WordPress setup. We'll focus on Next.js, a popular choice for headless WordPress implementations.

### Next.js Pages Router
The Pages Router is Next.js' traditional routing system, offering a straightforward file-based routing approach.

Structure:

```bash
pages/
  ├─ index.js
  ├─ [slug].js
  └─ posts/
       └─ [slug].js
```

Example:
```javascript
// pages/[slug].js
export async function getStaticPaths() {
  const { data } = await client.query({
    query: gql`
      {
        pages {
          nodes {
            uri
          }
        }
      }
    `
  });
  return { 
    paths: data.pages.nodes.map(page => ({ params: { slug: page.uri } })),
    fallback: 'blocking'
  };
}
```

### Next.js App Router
The App Router is Next.js' newer, more flexible routing system, offering advanced features like nested layouts and server components.

Structure:

```bash
app/
  ├─ page.js
  ├─ layout.js
  └─ (content)/
      ├─ [...slug]/page.js
      └─ posts/
           └─ [...slug]/page.js
```

Example:
```javascript
// app/layout.js
export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <Header />
        {children}
        <Footer />
      </body>
    </html>
  );
}
```

## Template Resolution Strategies
Implementing an effective template resolution strategy is crucial for accurately rendering WordPress content in your headless setup.

### Basic Template Detection
This approach uses a simple GraphQL query to determine the content type and render the appropriate component.

Here is an example:

```graphql
query GetTemplate($uri: String!) {
  nodeByUri(uri: $uri) {
    __typename
    ... on Page {
      isFrontPage
    }
  }
}
```
Then we use the following component to render an appropriate template(post or page):

```javascript
export default function ContentRouter({ data }) {
  const { __typename, isFrontPage } = data.nodeByUri;

  if (isFrontPage) return <HomePage />;
  
  switch(__typename) {
    case 'Post':
      return <Post data={data} />;
    case 'Page':
      return <Page data={data} />;
    default:
      return <NotFound />;
  }
}
```

## Advanced Template Hierarchy
For more complex setups, you can implement a more sophisticated template resolution system that mimics WordPress's template hierarchy.

The main idea is to use a specific query (called `seedQuery`) that resolves all the necessary information about the provided uri. Then we use a second function (called `getTemplate`) to determine the available templates for that route.

```graphql
query GetSeedNode($uri: String!) {
  nodeByUri(uri: $uri) {
    __typename
    ... on ContentNode {
      contentType {
        node {
          name
        }
      }
      fragment ContentNode on ContentNode {
          isContentNode
          slug
          contentType {
            node {
              name
            }
          }
          template {
            templateName
          }
      }
      fragment GetNode on UniformResourceIdentifiable {
        __typename
        uri
        id
        ...DatabaseIdentifier
        ...ContentType
        ...User
        ...TermNode
        ...ContentNode
        ...MediaItem
        ...Page
      }
    }
  }
}
```
Here is how to match a dictionary of compoments to the respective template type:

```javascript
const templateMap = {
  'front-page': FrontPage,
  // Default mappings
  'Post': StandardPost,
  'Page': StandardPage
};

export function getTemplate(seedNode, templates) {
  if (!seedNode) return null;

  const templatePriority = [
    seedNode.template && `template-${seedNode.template.templateName}`,
    seedNode.isFrontPage && 'front-page',
    seedNode.isPostsPage && 'home',
    seedNode.isTermNode && `taxonomy-${seedNode.taxonomyName}`,
    seedNode.contentType && seedNode.contentType.node && seedNode.contentType.node.name === 'page' && 'page',
    seedNode.contentType && seedNode.contentType.node && seedNode.contentType.node.name === 'post' && 'single',
    seedNode.isContentNode && 'singular',
    'index'
  ].filter(Boolean);

  for (const template of templatePriority) {
    if (templates[template]) {
      return templates[template];
    }
  }

  return null;
}
const seedNode = await getSeedNode(uri);
const template = getTemplate(seedNode, templateMap);
```
This template resolver function dynamically selects the appropriate React component to render based on WordPress content data. It replicates WordPress's template hierarchy logic for headless content by analyzing content properties from a GraphQL seedQuery result. It then returns the first resolved template compoent which we can render in the page.

For example, a standard blog post would look for a `single` template first before falling back to `singular` and then `index`.

However, this approach comes also with several important caveats:

1. **Limited template hierarchy**: This function doesn't fully replicate WordPress's complex template hierarchy. It only checks for custom templates and then falls back to content type, which may not cover all use cases.

2. **Query and variable handling**: The function doesn't address the need for specific queries or variables that a template might require. Developers must ensure that necessary data is fetched separately and passed to the resolved template component.

3. **Shared component queries**: Common components like headers and footers, which often require their own data fetching, aren't considered in this approach. It's more efficient to handle these as separate, reusable components with their own data fetching logic.

## Handling custom post types in a headless setup
In a headless setup, the frontend needs to fetch CPTs explicitly via GraphQL and define custom routes accordingly. For example, in Next.js pages router, you can pre-fetch CPT pages with `getStaticPaths()`:

```javascript
export async function getStaticPaths() {
  const { data } = await client.query({
    query: gql`
      {
        portfolios {
          nodes {
            uri
          }
        }
      }
    `
  });

  return {
    paths: data.portfolios.nodes.map(portfolio => ({
      params: { slug: portfolio.uri },
    })),
    fallback: 'blocking'
  };
}
```
On the frontend, ensure the proper template is used based on the GraphQL response:

```javascript
export default function ContentRouter({ data }) {
  const { __typename } = data.nodeByUri;

  switch (__typename) {
    case 'Post':
      return <Post data={data} />;
    case 'Page':
      return <Page data={data} />;
    case 'Portfolio':
      return <Portfolio data={data} />;
    default:
      return <NotFound />;
  }
}
```
## Handling query parameters in a headless setup
Query parameters (`?s=search`, `?cat=3`, etc.)  are used in special cases for example search, categories, and filtering content. In a headless setup, these parameters must be explicitly handled in the frontend framework.

### Search Queries (?s=search-term)
WordPress typically resolves search queries at `/index.php?s=search-term`. In a headless setup, you need to pass the search term to a GraphQL query:

```graphql
query SearchPosts($search: String!) {
  posts(where: { search: $search }) {
    nodes {
      title
      uri
    }
  }
}
```
In Next.js, extract the search term from the URL and fetch results:

```javascript
import { useRouter } from 'next/router';

export default function SearchPage() {
  const router = useRouter();
  const { s } = router.query;  

  const { data } = useQuery(SEARCH_POSTS_QUERY, { variables: { search: s } });

  return (
    <div>
      <h1>Search Results</h1>
      {data?.posts.nodes.map(post => (
        <div key={post.uri}>
          <a href={post.uri}>{post.title}</a>
        </div>
      ))}
    </div>
  );
}
```
## Category & Taxonomy Queries (?cat=3, ?tag=wordpress)

Category-based URLs like `/category/news/` get internally rewritten by WordPress as `index.php?category_name=news`. In a headless setup, we must manually query posts based on category:

```graphql
query GetPostsByCategory($categoryName: String!) {
  posts(where: { categoryName: $categoryName }) {
    nodes {
      title
      uri
    }
  }
}
```
To handle this in Next.js, extract the category from the URL and pass it as a query variable:

```javascript
export async function getServerSideProps({ query }) {
  const { category } = query;
  const { data } = await client.query({
    query: GET_POSTS_BY_CATEGORY,
    variables: { categoryName: category }
  });

  return { props: { posts: data.posts.nodes } };
}
```
**Note**: The use of `getServerSideProps` instead of `getStaticProps` is necessary because query parameters (such as `?category=news`) are not available inside `getStaticProps`. Since `getStaticProps` only runs at build time and does not have access to request-time data, `getServerSideProps` must be used to dynamically handle query parameters at request time.