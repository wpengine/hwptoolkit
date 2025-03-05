# Example: Fetching data from WordPress with Apollo Client in Next.js

## Overview

This example demonstrates various approaches to integrate WordPress as a headless CMS with a Next.js frontend using Apollo Client. It showcases different data fetching strategies, state management techniques, and modern web development patterns.

The examples cover everything from basic data fetching to advanced patterns like hybrid rendering, pagination, and real-time search functionality. Whether you're building a blog, news site, or content-driven application, these examples provide production-ready patterns for WordPress and Next.js integration.

## Installation and Setup

### Prerequisites

1. Node.js 18.18 or later
2. npm or other package manager
3. A WordPress installation with WPGraphQL plugin installed

### Clone the repository

```bash
git clone https://github.com/wpengine/hwptoolkit.git
cd examples/nextjs-pages-router/apollo-client-data-fetch
```

### Install dependencies

```bash
npm install
```

### Environment Setup

```bash
# Create a .env.local file with the following variables
NEXT_PUBLIC_WORDPRESS_URL=your_wordpress_blog_url
```

### Start the development server

```bash
npm run dev
```

### Navigate the corresponding url for each example

You can find the corresponding url under the `How to use` section of each example

## Examples

### 1. listCategoriesServerSide

This page implements [Server-side Rendering (SSR)](https://nextjs.org/docs/pages/building-your-application/rendering/server-side-rendering) feature of Next.js. It fetches the list of categories from WordPress and renders them on server-side.

#### Key Features

- Uses a simple GQL query to fetch data from the WordPress
- Uses `getServerSideProps` for server-side data fetching

#### How to use

- Go to [`http://localhost:3000/listCategoriesServerSide`](http://localhost:3000/listCategoriesServerSide)

---

### 2. getPostClientSide

This page implements [Client-side Data Fetching](https://nextjs.org/docs/pages/building-your-application/data-fetching/client-side) and [Dynamic Routes](https://nextjs.org/docs/pages/building-your-application/routing/dynamic-routes) feature of Next.js. It utilizes Apollo Client with WPGraphQL to fetch individual blog posts, handling loading states, error management, and dynamic routing functionality.

In this page we also use [`createFragmentRegistry`](https://www.apollographql.com/docs/react/data/fragments#registering-named-fragments-using-createfragmentregistry) feature of Apollo Client. It allows us to include fragments into `InMemoryCache` of our `ApolloClient` instance.

```javascript
export const client = new ApolloClient({
  // ...
  cache: new InMemoryCache({
    fragments: createFragmentRegistry(gql`
      fragment PostFragment on Post {
        id
        databaseId
        uri
        title
        date
        author {
          node {
            name
          }
        }
      }
    `),
  }),
});
```

Then use it in the queries without declaring the fragment itself.

```graphql
const GET_POST = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      content
      ...PostFragment
    }
  }
`;
```

#### Key Features

- Demonstrates parameter-based data fetching
- Uses `useQuery` hook for client-side data fetching
- Implements dynamic routing with Next.js `useRouter`
- Implements loading state and error handling
- Uses GraphQL fragments for consistent post data structure

#### How to use

- Go to `http://localhost:3000/getPostClientSide/YOUR_POST`
- Don't forget to replace "YOUR_POST" with your preferred post slug

---

### 3. getPostServerSide

This page achieves the same goal with GetPostClientSide, except handling the data fetching and rendering on server side. In this example do the error handling on back-end, and in case of an error returning the [not-found component of Next.js](https://nextjs.org/docs/app/api-reference/file-conventions/not-found).

```javascript
export async function getServerSideProps({ params }) {
  // ...

  if (!data?.post)
    return {
      notFound: true,
    };

  // ...
}
```

#### Key Features

- Uses dynamic routing with `getServerSideProps` for post slugs
- Implements 404 handling for non-existent posts and errors in SSR context
- Uses GraphQL fragments for consistent post data structure

#### How to use

- Go to `http://localhost:3000/getPostServerSide/YOUR_POST`
- Don't forget to replace "YOUR_POST" with your preferred post slug

---

### 4. getPageStatic

This page implements [Static Site Generation (SSG)](https://nextjs.org/docs/pages/building-your-application/data-fetching/get-static-props) feature of Next.js. The page utilizes `getStaticProps` to fetch data and pre-render the page at build time.

```javascript
export async function getStaticProps() {
  const { data } = await client.query({
    query: GET_PAGE,
    variables: {
      id: PAGE_URI,
    },
  });

  return {
    props: {
      data,
    },
  };
}
```

#### Key Features

- Implements Static Site Generation with `getStaticProps`
- Fetches WordPress page using page URI

#### How to use

- Replace the `PAGE_URI` with the URI of your preferred page
- Go to: [`http://localhost:3000/getPageStatic`](http://localhost:3000/getPageStatic)

---

### 5. searchPostsHybrid

This page implements a hybrid approach combining [Server-Side Rendering (SSR)](https://nextjs.org/docs/pages/building-your-application/data-fetching/get-server-side-props) with [Client-Side Data Fetching](https://nextjs.org/docs/pages/building-your-application/data-fetching/client-side) in Next.js. It provides a real-time search functionality for blog posts using Apollo Client and WPGraphQL, with initial data being server-rendered to improve the performance and SEO.

The search functionality utilizes [`useLazyQuery`](https://www.apollographql.com/docs/react/data/queries#manual-execution-with-uselazyquery) from Apollo Client for on-demand data fetching, combined with debouncing for performance optimization. Initial posts are fetched server-side to provide immediate content on page load.

#### Server-side initial data fetch using `getServerSideProps`

```javascript
export async function getServerSideProps() {
  const { data } = await client.query({ query: SEARCH_POSTS });
  const posts = data?.posts ?? [];

  return {
    props: {
      initialPosts: posts,
    },
  };
}
```

#### Debounced search input to prevent excessive API calls

```javascript
const onSearch = (searchString) =>
  getPosts({
    variables: {
      search: searchString,
    },
  });

return (
  <>
    <SearchInput onSearch={debounce(onSearch, 200)} />

    {posts?.edges?.map((item) => {
      const post = item.node;

      return <BlogPostItem key={post.id} post={post} />;
    })}
  </>
);
```

#### Client-side search updates using `useLazyQuery`

By setting [fetchPolicy](https://www.apollographql.com/docs/react/data/queries#setting-a-fetch-policy) to `cache-and-network` we ensure that the query will first return cached data and update the cache on each network request. In case of dynamic search, this feature ensures consistend UI updates.

```javascript
const [getPosts, { data }] = useLazyQuery(SEARCH_POSTS, {
  fetchPolicy: "cache-and-network",
});

const posts = data ? data?.posts : initialPosts;
```

#### Key Features

- Hybrid data fetching approach (SSR + CSR)
- Real-time search functionality
- Debounced search input
- Using `cache-and-network` fetchPolicy for consistend UI updates
- Seamless transition between server and client-side data
- Fallback to initial posts when search is empty

#### How to use

- Go to [`http://localhost:3000/searchPostsHybrid`](http://localhost:3000/searchPostsHybrid)
- Type your search query into search box

---

### 6. listPostsPaginated

This page implements [Cursor-based Pagination of WPGraphQL](https://www.wpgraphql.com/docs/graphql-queries#pagination) using Apollo Client. It demonstrates the "Load More" pagination pattern, fetching posts in batches of 10.

The implementation utilizes Apollo Client's built-in cache management to combine incoming page into existing pages. [`relayStylePagination`](https://www.apollographql.com/docs/react/pagination/cursor-based#relay-style-cursor-pagination), a helper function of Apollo Client, which comes out of the box helps us to implement this logic. You can find this configuration in `ApolloClient` client instance.

```javascript
export const client = new ApolloClient({
  // ...

  cache: new InMemoryCache({
    typePolicies: {
      Query: {
        fields: {
          posts: relayStylePagination(),
        },
      },
    },
  }),

  // ...
});
```

The page leverages Apollo's `fetchMore` functionality to handle pagination. We pass the `endCursor` parameter to the query in order to get the next batch of posts.

```javascript
const { loading, data, fetchMore } = useQuery(LIST_POSTS_PAGINATED);
const { endCursor, hasNextPage } = data?.posts?.pageInfo ?? {};

// ...

const loadMore = () => fetchMore({ variables: { after: endCursor } });
```

#### Key Features

- Client-side data fetching with Apollo Client
- Implementing cursor-based pagination of WPGraphQL
- Implementing relayStylePagination helper of Apollo Client
- "Load More" button for manual pagination

#### How to use

- Go to [`http://localhost:3000/listPostsPaginated`](http://localhost:3000/listPostsPaginated)

---

### 7. addCommentToPost

This page implements a dynamic blog post view with comments functionality. It uses GraphQL mutations for comment submission with [`useMutation hook`](https://www.apollographql.com/docs/react/data/mutations). In this example we're using `no-cache` policy to disable caching on mutation. Setting `errorPolicy` to `all` helps us to handle mutation errors gracefully.

```javascript
const [addComment, { data: commentData, loading: addingComment, error: commentError }] = useMutation(
  ADD_COMMENT_TO_POST,
  {
    errorPolicy: "all",
    fetchPolicy: "no-cache",
  }
);
```

The implementation uses the `CommentFragment` from fragment registry, to fetch the comments along with the post.

```javascript
const GET_POST = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      ...PostFragment
      content
      comments {
        edges {
          node {
            ...CommentFragment
          }
        }
      }
    }
  }
`;
```

#### Key Features

- Dynamic post loading
- Using `useMutation` hook to submit a comment
- Graceful error handling for the mutation
- Using `errorPolicy` and `fetchPolicy`

#### How to use

- Go to `http://localhost:3000/addCommentToPost/YOUR_POST`
- Don't forget to replace "YOUR_POST" with your preferred post slug
- Fill in the fields and post your comment
- Fill in the exact same data and submit the comment to see error handling. Hint: it will show the "Duplicate comment" error.
