# Routing in Headless WordPress: A Comprehensive Guide

This guide explores the intricacies of implementing routing in a headless WordPress setup. We'll cover the core challenges, possible implementations, and advanced considerations for optimizing your headless WordPress site.

## Background

Other headless CMSes don't have strong opinions, they leave those the the front-end framework. A common pattern is to give content only a `slug` (i.e `my-new-blog-post`). This usually only has to be unique within a specific content type. It's up to the framework to then make that unique across all content by building a full `URI` (i.e. `/blog/my-new-blog-post`).

This works great. The backend CMSes don't have much for an opinion, and the front-end does.

## WordPress: Built Different

When it comes to routing, WordPress differs from many other headless CMSes in one core aspect. It has an opinion.

WordPress also uses slugs. But,WordPress has its own theming and templating system, because of this it also generates URIs. These can be fully customized and are called "Permalinks". We'll get in to the specifics of those in a moment, but for now, know that these URIs are used throughout WordPress.

For example, if you want to add a link to a page in the editor, you can start typing the name of an article and it'll autocomplete the link for you. With the URI of that article based on the permalink configuration. Menus work the same way.

Because of this it's very difficult to ignore the WP Permalink system. Editors and content publishers don't want to be hand typing links constantly. It's best not to fight this system.

At a minimum in headless WordPress, we must get our front-end framework routing to agree with WordPress Permalinks. So, start your routing journey by configuring your WordPress Permalinks to generate the correct URLs for your content! Once that's done we can delve into the two primary ways you can make your front-end framework route based on those permalinks.

## Solution #1: File System Routers

Modern front-end frameworks all have various ways you can define the routes of your application. Most have settled on some form of file-system based routing. These opinionated routing systems expect to be the source of source of truth for routing.

> [!NOTE]
> Gatsby is one of the few (only?) where you can programmatically connect content from a CMS to a template. A sadly under-valued feature. Though this can be finagled in to many frameworks using rewrites or other methods as we will discuss.

### WP Config

The following examples assumes our WP Posts Permalinks are configured to exist at `/posts/%postname%/`. Out `pages/posts/[slug].js` file route will now provide a template for rendering the posts.

> [!IMPORTANT]
> In WordPress Permalinks system the `%postname%` variable is used for `slug`. This is a artifact of the fact that by default slugs are generated from the post name. Though an author is free to put in a custom slug. These terms will be used interchangeably.

By default in WordPress Pages will render with no prefix, just using their slug. Therefore, the `pages/[slug].js` file route will provide a template for these.

### Using the slug 🐌

The all file system routers are slightly different, we'll be using pseudo code here to illustrate our point, this code won't work as-is in any specific framework.

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
// pages/[slug].js & pages/posts/[slug].js
export async function getProps({ params }) {
  const { slug } = params; // slug: [ "you-post-slug" ]
  const content = await fetchPageContent(slug.join("/"));
  return { props: { content } };
}
```

#### GraphQL Requests

Now that we've got routing working let's dive into the specifics of actually fetching our content based on this routing. As our example above shows, Next provides us with the slug based on our dynamic route. This can be passed as a variable to a WPGraphQL requests:

```graphql
# For Posts
query getPostContent($slug: ID!) {
  post(id: $slug idType: SLUG) {
    title
    content
    ...
  }
}

# For Pages
query getPageContent($slug: ID!) {
  page(id: $slug idType: SLUG) {
    title
    content
    # ...
  }
}
```

The post gets fetched! But for our posts we would see an error `"message": "Value \"SLUG\" does not exist in \"PageIdType\" enum."`. Why is it that our `idType: SLUG` is valid for Posts but not Pages?

#### Under the hood with WordPress slugs

This error happens because WordPress has a feature called Nested Pages. This enables creating nested routes such as `/about/george` and `/about/cynthia`. All within the same post type.

> [!IMPORTANT]
> WordPress "Pages" are posts. Yes it's confusing. WordPress under the hood has the concept of "Post Types". You can make custom post types with various features. The default Pages and Posts are simply default post types.

In our example above the actual slugs for `/about/george` and `/about/cynthia` are `george` and `cynthia` the `/about` page contains the slug `about`. This means I could also have a page at URI `/locations/florida/about` and the SLUG would also be `about`.

Meaning, on any nestable post type (which Pages are), slugs are NOT unique. Thus, GraphQL doesn't let us use them as a unique ID to fetch the content.

Finally, it's important to know that there can also be collisions between Posts, Pages, and any custom post types if they only use slug (`%postname%`) for permalinks. This is why GraphQL doesn't let us use the `contentNode` entry point to make the same request across post types.

```graphql
# Invalid Query
query getRouteContent($slug: ID!) {
  contentNode(id: $slug idType: SLUG ) {
    # ...
  }
}
```

#### A Possible Solution

So, What's the solution? Well if we stick with slugs we have 2 options:

1.  Disable Nested Pages.
2.  Use the URI.

While disabling nested pages is a valid option it's a band-aid to a long series of issues if you're making heavy use of WordPress. Using URIs, while counter intuitive, solves more issues.

### Using the URI

Example:

```javascript
// pages/posts/[slug].js
export async function getProps({ url }) {
  const content = await fetchPageContent(url.pathname);
  return { props: { content } };
}
```

Using the URI (aka. path name) for the content resolves all of our issues. URIs are unique across all content and post types in WP and nested pages can now be used.

```graphql
# For Posts
query getPostContent($uri: ID!) {
  post(id: $uri idType: URI) {
    title
    content
    ...
  }
}

# For Pages
query getPageContent($uri: ID!) {
  page(id: $uri idType: URI) {
    title
    content
    # ...
  }
}
```

Yay! These requests are all valid and return the correct content! This use of URIs gives every piece of content a unique template and fixes our previous routing issues. It leaves a lot of duplicate code and manual work. Let's see if we can improve

### TL;DR

| Pro ✅                                  | Cons ❌                                                |
| --------------------------------------- | ------------------------------------------------------ |
| Uses common file-system router patterns | Significant duplicate code                             |
| Simplest routing method                 | Must be kept in-sync with WordPress                    |
| --                                      | Potential for large bundles                            |
| --                                      | High Potential for performance and low cache hit rates |

In the end, the slug based approach works best for simpler uses of Headless WordPress. If you have an existing site driven by a JS framework that you're only trying to add a blog to, the slug based approach works great! You won't be using pages or any other nestable post type, collisions with other content types are avoided, and the implementation is the easiest we know of.

However, if you're goal is to drive a significant portion of you site routes using headless WordPress, slugs become a loosing battle. Moving to URIs does solve the immediate issues while introducing some new ones. It's now up to the developer to make sure that the WordPress Permalink configuration is kept in-sync with the File System router of the frontend.

Because File System routers also corelate 1 URI or URI structure to a single template, any new post types in WordPress require code changes in your JS frontend. In our nested pages example above, maybe we decide the `about` page should have a different template. While it is possible to accomplish this with the File System router it's a constant sync. What if someone in WP decides to change the `about` slug to `about-us`. Our custom template breaks and code changes are required.

In the end, the File System router will always be at odds with WP Permalinks and limit the flexibility of the WordPress CMS. Our best bet is going to be to hand off as much of this to WordPress without having to synchronize the two routing systems.

## Solution #2: Programmatic Routing

In WordPress we have 3 distinct pieces that affect what gets rendered given a URL. You have the content itself (posts,pages, media, authors, archives, etc...), the Permalink config, and templates. Permalinks determine the location (i.e. URI) for a given piece of content and templates determine how to visually render that content. In other words, Permalinks determine **where** to render content and templates determine **how** to render content.

When a request comes into WordPress it uses the URI to ask for the piece of content. Given various metadata about that content it can then choose a template from those available to render the content. **Thus the key difference is the shift from file-based to data-driven template selection.**

WPGraphQL's `pageByUri` gets us the content just like WordPress. That's a great first step to relying on WordPress for routing. Let's update our theoretical code to catch all URLs and send them to WordPress for the content:

### Advanced URI usage

Structure:

```bash
pages/
  └─ [[...uri]].js # This
```

GraphQL:

```graphql
query getPageContent($uri: String!) {
  nodeByUri(uri: $uri) {
    __typename
    ... on NodeWithTitle {
      title
    }
    ... on NodeWithContentEditor {
      content
    }
  }
}
```

Now that we've fetched the content we can render it:

React:

```javascript
export default function ContentRouter({ data }) {
  const { __typename, isFrontPage } = data.nodeByUri;

  if (isFrontPage) return <HomePage />;

  switch (__typename) {
    case "Post":
      return <Post data={data} />;
    case "Page":
      return <Page data={data} />;
    default:
      return <NotFound />;
  }
}
```

This works! But this basic implementation has several related issues. Because File system routers equate a single route to a single template, all content now has only one template. That template defines our GraphQL query, page layout, etc. React and GraphQL are perfectly capable of solving this, as shown, but that also comes with some caveats.

1. All templates end up in a single JS bundle because code spiting is done based on file system routes.
2. A single large GraphQL query doesn't cache well causing performance issues due to low cache hit rate.

All this has solutions but we'll talk about those next.

### The Seed Query

The concept of a "Seed Query" is to get only the information needed to decide what template the content will use to be rendered. This is what WordPress does under the hood before rendering a template. In headless this GraphQL request is extremely small. This reduced size means it's relatively stable and can benefit from a high cache-hit rate.

```graphql
query GetSeedNode($uri: String!) {
  nodeByUri(uri: $uri) {
    __typename
  }
}
```

This seed query is as simple as we can make it. Templates are still be selected based on content type. The difference is now a follow up GraphQL query is made for the full content based on exactly what our template needs. This could look something like:

```javascript
// pages/[[...uri]]].js
export async function getProps({ params })
  const { uri } = params;

  const { seedData: nodeByUri } = await getSeedQueryData(uri);

  const templateQuery = getTemplateQuery(seedData.__typename);

  const content  = await getTemplateData(templateQuery, { uri })

  return { props: { content } };

```

The SeedQuery helps us resolve the issue of a single large GraphQL request. This is very simple example. In reality this can be much more complex. WordPress uses its own [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) system for resolving any given content to a number of possible templates.

This template hierarchy is very powerful. While potentially over powered for many use-cases it does solve many problems and have corelation to some configuration options in WordPress. Since we're we've decided to make WordPress our authority on routing, let's make it our authority on templates as well.'

### The WordPress Template Hierarchy

The WordPress template hierarchy is a powerful feature that determines which template file to use for rendering content. Replicating this in a headless setup requires a different approach.

WordPress uses the a variety of data (content type, post type, selected template, id, slug, configuration, etc ) to decide which template or set of templates should be used to display the page. If WordPress cannot find a template file with a matching name, it will skip to the next file in the hierarchy. If WordPress cannot find any matching template file, the theme’s `index.php` file will be used. For example:

```text
single-post.php → single.php → singular.php → index.php
```

> [!NOTE]
> The WordPress template hierarchy and all of its intricacies are out of scope for this guide. Please checkout the [WordPress documentation](https://developer.wordpress.org/themes/basics/template-hierarchy/) for more details.

In headless WordPress we can use GraphQL queries to fetch template information. Then we can chose to simulate the template hierarchy resolution logic to determine the appropriate component to render.

#### By Example

We've implemented this method in Faust.js already. Instead of copying the full code here let me highlight the important points and provide links. To make any template resolution work we a way to register available templates and their GraphQL queries, and a way to determine which templates a piece of content could be rendered by given the SeedQuery data.

##### Faust SeedQuery

Faust's Seed query can be found on the repo @ [packages/faustwp-core/src/queries/seedQuery.ts](https://github.com/wpengine/faustjs/blob/canary/packages/faustwp-core/src/queries/seedQuery.ts). You'll notice it's quite a bit more complex than shown above. There's a whole assortment of fields that are required to implement WordPress's full Template Hierarchy.

Given our earlier discussion of cacheability, you may be concerned about this growing query. Don't worry, it's still highly cacheable.

##### Faust Template(s)

Once the data is fetched that's passed to [a function](https://github.com/wpengine/faustjs/blob/canary/packages/faustwp-core/src/getTemplate.ts#L5-L147) that uses the data to create a list of all possible templates, in order of importance, a given piece of content might use.

This list might look something like:

```json
["page-my-slug", "page", "page-23", "singular", "index"]
```

##### Resolving Faust Templates

In faust we uses a JS object to register templates. These templates (React files in this case) have graphql queries attached to them already.

```javascript
import IndexTemplate from "./index-template";
import page from "./page";

export default {
  index: IndexTemplate,
  page,
};
```

Once a piece of content's possible templates are resolved, my code can look for the first matching template that actually exists.

#### TL;DR

Leveraging the WordPress template hierarchy means any configuration on WordPress norms will role over into our front-end.

Let's say I decide to add a new custom post type to WordPress. I've already have a `single`, `page`, `archive`, and `author` templates in my front-end app. This new post type will just work. I don't have to add new routes or templates. I can choose to customize my existing template or add a whole new one to better render my custom post pages, but the author pages and archive pages can leverage the existing templates.

Alteratively, Let's say I've been using the default WP config of post archive for my home page. This was using a `home` or `index` template. Simply by changing the configuration in WordPress to use a specific Page, that page will now be rendered on the using the `page` template I already have. Again, I can opt to customize this or add a `front-page` template, but I don't have to.

For agencies, this abstracts theming and templating from the data fetching and minutia of making a headless app. You could have the same core application for every client!

### Code Splitting

We still have the issue of code splitting. Up until now we've leveraged React code to handle template resolution. Unfortunately that means all available templates are being shipped in our catch-all route (`/pages/[[...uri]].js`). This means the code that builds the front page, posts, pages, archives, author pages, etc. all gets shipped together on any one of those URL requests.

As is the benefit of client side routing, this is only a one time cost. Once that first page is loaded, all subsequent requests used the cache bundle. But performance on first page load is very important. It's why JS frameworks do bundling. They're trying to ship only minimum required code to render a page.

There are a couple situations where this impact is extremely light. First, the scale of this problem is directly correlated to the unique code required to render each of your routes. If you have 4 templates that share a large number of components and dependencies, the amount of unique code probably sites in the low Kilobytes. This doesn't mean you wouldn't benefit from code splitting. It just means your visitors won't see significant differences in performance with or without.

The more your various template have unique components and dependencies, the more of a performance impact this will have. That said, Faust.js currently ships with this deficiency. All templates are bundled together. Many sites ship just fine with excellent performance. This might vary much be a "pick you battles" issue. But for the sake of being thorough, let's talk about some possible solutions.

If you're not using a client-side router like in Next.js pages, SvelteKit, or Nuxt, you're okay. The server will handle this on ship the correct HTML. Frameworks like Gatsby, Astro, and server only modes of SvelteKit support this. But this isn't the norm. If you are shipping client-side code, there are solutions!

## Solving code Splitting

Let's be clear. This isn't a routing problem. That's been solved with slugs and URIs. We've solved templating but because of the deficiencies of File System based routers we're stuck with a bundling problem. This problem exists in frameworks that don't allow for any form of programmatic routing. Gatsby is the only main-stream JS framework that did this well. Gatsby actually added the File System routing LATER, and it was an abstraction of the lower level APIs you still had access to. Alas, Gatsby is not for everyone, and we can't really recommended for anyone given its state of maintenance, or lack there of. For the big frameworks (Next, Nuxt, SvelteKit, Astro, etc) there are a couple common features that can help resolve the bundling issue.

- Rewrites
- Dynamic Imports

### Rewrites & Middleware

Framework specific implementations vary but from middleware or a catch-all route you should be able to intercept an in coming request and rewrite to a hidden page.

> [!INFO]
> A rewrite is like a redirect, but it's internal to the server. If I rewrite a page from from `/about` to `/404` the browser url remains `/about` while rendering `/404`.

Initial experimentation shows using a catch-all route to handle the seed query and rewrite to be a better solution. This allows for leveraging existing routing systems and linear thinking through routes. Middleware will work but because it is triggered on every request, including rewrites, its recursive in nature. This can make its use with rewrites quite confusing.

Use of middleware also seems to eject of from all provided routing mechanisms. If I am creating routes in my JS app from multiple data sources I have to account for that in my middleware routing. By using a catch-all route to handle the re-write I can still leverage the standard File System router for those other routes and the rewrite logic will never be called for non-WordPress routes.

### Dynamic Imports

Async or dynamic imports using `import()` in the browser are another possible solution. This viability of this solution really depends on the framework.

Next Pages router made this very difficult, but better async support in the Next App router using React Server Components might make this really easy.

Most frameworks will also pre-fetch appropriate bundles when links requiring them enter the page for a better experience. Without more experimentation we suspect dynamic imports would break this pattern. Possibly causing worse performance than a single large bundle.

## Conclusion

How you route with headless WordPress is best done with URIs. But how your go from URI => Content => Template very much depends on what JS framework you use and what the needs of your app are. We hope you have a better understanding of the problems and solutions that are out there for routing with Headless WordPress.

Let us know if we missed anything or you have more questions!

## Appendices

### Handling query parameters in a headless setup

Query parameters (`?s=search`, `?cat=3`, etc.) are used in special cases for example search, categories, and filtering content. In a headless setup, these parameters must be explicitly handled in the frontend framework.

#### Search Queries (?s=search-term)

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
import { useRouter } from "next/router";

export default function SearchPage() {
  const router = useRouter();
  const { s } = router.query;

  const { data } = useQuery(SEARCH_POSTS_QUERY, { variables: { search: s } });

  return (
    <div>
      <h1>Search Results</h1>
      {data?.posts.nodes.map((post) => (
        <div key={post.uri}>
          <a href={post.uri}>{post.title}</a>
        </div>
      ))}
    </div>
  );
}
```

#### Category & Taxonomy Queries (?cat=3, ?tag=wordpress)

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
    variables: { categoryName: category },
  });

  return { props: { posts: data.posts.nodes } };
}
```

> [!NOTE]
> The use of `getServerSideProps` instead of `getStaticProps` is necessary because query parameters (such as `?category=news`) are not available inside `getStaticProps`. Since `getStaticProps` only runs at build time and does not have access to request-time data, `getServerSideProps` must be used to dynamically handle query parameters at request time.

### Understanding native WordPress routing mechanism

WordPress determines the content to display based on URL structure, using `wp_rewrite` and query parameters. The core routing rules rely on:

- Pretty Permalinks (e.g., `/blog/my-post/`)

- Query String-Based Routing (e.g., `/index.php?post_type=post&p=123`)

- Rewrite Rules (e.g., `/category/news/` maps to `index.php?category_name=news`)

### How custom post types impact routing

In traditional WordPress, custom post types (CPTs) are registered using `register_post_type()` and automatically get rewrite rules based on their settings. For example, a CPT called `portfolio` with the `has_archive` option enabled might have URLs like:

- `/portfolio/` → Archive page for portfolio items
- `/portfolio/project-name/` → Single portfolio item
