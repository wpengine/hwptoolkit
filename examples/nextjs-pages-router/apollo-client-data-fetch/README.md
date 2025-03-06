# Next.js Headless WordPress with Apollo Client

This example demonstrates various approaches to integrate WordPress as a headless CMS with a Next.js frontend using Apollo Client. It showcases different data fetching strategies, state management techniques, and modern web development patterns in a real-world application context.

## Features

- **Covers various rendering patterns of Next.js**

  - Server-Side Rendering (SSR) for dynamic pages
  - Static Site Generation (SSG) for static pages
  - Hybrid data fetching, combining SSR and CSR

- **Blog features**

  - Listing posts with pagination
  - Live search of posts
  - Fetching posts and pages using nodeByUri of WPGraphQL
  - Fetching static pages at build time
  - Commenting posts

- **Apollo Client integration**
  - Relay-style pagination
  - Fragment management
  - Error handling
  - Custom fetch policies
  - Custom error policies
  - useLazyQuery example
  - useMutation example

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
