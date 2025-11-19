---
title: "Build Previews with Next.js and WPGraphQL"
description: "Learn how to build a Next.js application with WordPress preview functionality using WPGraphQL and the HWP Previews plugin."
---

## Overview

In this tutorial, we will build a Next.js application that displays WordPress content and enables preview functionality for draft posts. By the end, you will have a working headless WordPress setup where clicking "Preview" in WordPress opens your draft content in Next.js.

We will use Next.js Draft Mode, WPGraphQL for data fetching, and WordPress Application Passwords for authentication.

> [!TIP]
> You can see the completed project in the [hwp-preview-wpgraphql example](https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews/examples/hwp-preview-wpgraphql).

## What you'll build

By following this tutorial, you will create:

* A Next.js application that fetches WordPress content via GraphQL
* An API route that enables Next.js Draft Mode for previews
* Preview functionality that shows draft content when you click "Preview" in WordPress
* Authentication using WordPress Application Passwords


## Prerequisites

Before starting, make sure you have:

* Node.js 18 or higher installed
* A WordPress site with HWP Previews and WPGraphQL plugins installed
* Basic familiarity with Next.js and React

## Step 1: Create the Next.js application

First, we will create a new Next.js project.

Open your terminal and run:

```bash
npx create-next-app@latest my-wordpress-preview
```

When prompted, select:
* TypeScript: No
* ESLint: Yes
* Tailwind CSS: Yes (optional)
* App Router: No (we'll use Pages Router)

Navigate into your project:

```bash
cd my-wordpress-preview
```

You should now see a basic Next.js project structure with a `pages` directory.

## Step 2: Install Apollo Client

We will use Apollo Client to fetch data from WordPress via GraphQL.

Install the required packages:

```bash
npm install @apollo/client graphql
```

Notice that your `package.json` now includes these new dependencies.

## Step 3: Set up Apollo Client

Now we will create an Apollo Client instance to connect to WordPress.

Create a new file `src/lib/client.js`:

```javascript
import { ApolloClient, HttpLink, InMemoryCache } from "@apollo/client";

const WORDPRESS_URL = process.env.NEXT_PUBLIC_WORDPRESS_URL;

export const client = new ApolloClient({
  link: new HttpLink({
    uri: WORDPRESS_URL + "/graphql",
  }),
  ssrMode: typeof window === "undefined",
  cache: new InMemoryCache(),
});
```

This creates a client that points to your WordPress GraphQL endpoint. Notice how we use an environment variable for the WordPress URL.

## Step 4: Create environment variables

Create a `.env.local` file in your project root:

```bash
NEXT_PUBLIC_WORDPRESS_URL=http://your-wordpress-site.com

WP_USERNAME=admin           # WordPress username which you created Application Password for
WP_APP_PASSWORD=****        # WordPress Application Password
WP_PREVIEW_SECRET=****      # Any strong secret string
```

Use your actual WordPress URL and username here. We will cover the Application Password and the secret in a later step.

## Step 5: Create the authentication utility

We need a way to send WordPress credentials with our preview requests.

Create `src/utils/getAuthString.js`:

```javascript
export function getAuthString() {
  const username = process.env.WP_USERNAME;
  const password = process.env.WP_APP_PASSWORD;

  if (!username || !password) {
    return null;
  }

  return "Basic " + Buffer.from(`${username}:${password}`).toString("base64");
}
```

This function creates a Base64-encoded authentication string from your WordPress credentials. You will use this when fetching preview content.

## Step 6: Create the preview API route

Now we will create the API route that enables Draft Mode when WordPress redirects to your preview.

Create `src/pages/api/preview.js`:

```javascript
import { client } from "@/lib/client";
import { getAuthString } from "@/utils/getAuthString";
import { gql } from "@apollo/client";

// GraphQL query to verify the content exists and get its database ID
const GET_CONTENT = gql`
  query GetNode($id: ID!) {
    contentNode(id: $id, idType: DATABASE_ID, asPreview: true) {
      databaseId
    }
  }
`;

export default async function handler(req, res) {
  const { secret, id } = req.query;

  if (!id) {
    return res.status(400).json({ message: "No ID provided." });
  }

  // Verify the secret token matches our environment variable for security
  if (secret !== process.env.WP_PREVIEW_SECRET) {
    return res.status(401).json({ message: "Invalid secret token." });
  }

  // Query WordPress to verify the content exists and we can access it
  const { data } = await client.query({
    query: GET_CONTENT,
    variables: { id },
    context: {
      headers: {
        Authorization: getAuthString(),
      },
    },
  });

  if (!data?.contentNode) {
    return res.status(404).json({ message: "Content not found." });
  }

  // Enable Next.js Draft Mode for this session
  res.setDraftMode({ enable: true });
  // Redirect to the content page using the database ID
  res.redirect("/" + data.contentNode.databaseId);
}
```

This route does three important things:

1. Checks if the secret token matches (security)
2. Verifies the content exists using GraphQL
3. Enables Draft Mode and redirects to the content

## Step 7: Create the content display page

We will create a dynamic page that displays both published and preview content.

Create `src/pages/[identifier].js`:

```javascript
import { client } from "@/lib/client";
import { getAuthString } from "@/utils/getAuthString";
import { gql } from "@apollo/client";

// This query handles both published content and preview content
// It uses GraphQL directives to conditionally fetch from different fields
const GET_CONTENT = gql`
  query GetSeedNode($id: ID! = 0, $uri: String! = "", $asPreview: Boolean = false) {
    nodeByUri(uri: $uri) @skip(if: $asPreview) {
      __typename
      ... on Post {
        title
        content
        date
      }
    }

    contentNode(id: $id, idType: DATABASE_ID, asPreview: true) @include(if: $asPreview) {
      __typename
      ... on Post {
        title
        content
        date
      }
    }
  }
`;

export default function Content({ data }) {
  if (!data) {
    return <div>Content not found</div>;
  }

  return (
    <article>
      <h1>{data.title}</h1>
      <div dangerouslySetInnerHTML={{ __html: data.content }} />
    </article>
  );
}

export async function getStaticProps({ params, draftMode }) {
  // Use different variables based on whether we're in draft mode
  const variables = draftMode
    ? { id: params.identifier, asPreview: true }
    : { uri: params.identifier };

  // Only send auth headers for preview requests
  const headers = draftMode ? { Authorization: getAuthString() } : null;

  const { data } = await client.query({
    query: GET_CONTENT,
    variables,
    context: { headers },
  });

  // Extract content from the appropriate field
  const content = draftMode ? data?.contentNode : data?.nodeByUri;

  return {
    props: { data: content },
    revalidate: 60,
  };
}

export async function getStaticPaths() {
  return {
    paths: [],
    fallback: "blocking",
  };
}
```

Notice how this page handles both preview mode (using `contentNode`) and normal mode (using `nodeByUri`). When Draft Mode is enabled, it sends authentication headers.

## Step 8: Generate a WordPress Application Password

Now we need to create an Application Password in WordPress for authentication.

1. Log into your WordPress admin
2. Go to Users > Profile
3. Scroll down to "Application Passwords"
4. Enter a name like "Next.js Preview"
5. Click "Add Application Password"

![WordPress Application Passwords section showing the form to generate a new application password with a name field and "Add Application Password" button](generate-application-password.png)

Copy the generated password (it will look like `xxxx xxxx xxxx xxxx xxxx xxxx`). You will not be able to see it again.

Update your `.env.local` file with this password:

```bash
WP_APP_PASSWORD=xxxx xxxx xxxx xxxx xxxx xxxx
```

## Step 9: Configure HWP Previews in WordPress

We will now configure the preview URL in WordPress to point to your Next.js app.

1. In WordPress admin, go to Settings > HWP Previews
2. Click the "Posts" tab
3. Check "Enable HWP Previews"
4. In the Preview URL Template field, enter:
   ```
   http://localhost:3000/api/preview?id={ID}&secret=YOUR_SECRET_TOKEN
   ```
5. Replace `YOUR_SECRET_TOKEN` with a random string (like `my-secret-preview-token-123`)
6. Click "Save Changes"

![WordPress HWP Previews settings page showing the Posts tab with "Enable HWP Previews" checkbox checked and a Preview URL Template field containing the localhost preview URL](configure-hwp-previews.png)

Update your `.env.local` file with the same secret token:

```bash
WP_PREVIEW_SECRET=my-secret-preview-token-123
```

## Step 10: Start your application

Start the Next.js development server:

```bash
npm run dev
```

You should see output indicating the server is running at `http://localhost:3000`.

## Step 11: Test the preview

Now we will test that previews work correctly.

1. In WordPress, create or edit a post
2. Make some changes but do not publish
3. Click the "Preview" button

You should be redirected to your Next.js application showing your draft content. Notice the URL includes your post ID.

![Screenshot showing a Next.js application displaying WordPress draft content in preview mode, with the post title and content visible on the page](preview-view.png)

If you see your draft content, congratulations! Your preview system is working.

## Next steps

Now that you have a working preview system, you can:

* Add support for Pages and custom post types
* Implement a "Disable Preview" button
* Add loading states and error handling
* Deploy your application to production

For more details about extending this setup, see the [complete example](https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-previews/examples/hwp-preview-wpgraphql) which includes these additional features.
