---
title: "Next.js Rendering WordPress Blocks"
description: "This example demonstrates rendering WordPress Blocks with JSX in a Next.js project. The example includes 16 block components across various categories. Example includes a utility to convert flat blocks data from GraphQL (GQL) response into the hierarchical data structure. Passing this data into BlockRenderer component generates the WordPress content by matching the appropriate blocks and using a default block when block implementation is missing. Default block is also customizable component. Example also gives an option to provide custom HTML parser to render HTML content."
---

# Example: Rendering WordPress Blocks in Next.js

This example demonstrates rendering WordPress Blocks with JSX in a Next.js project. The example includes 16 block components across various categories. Example includes a utility to convert flat blocks data from GraphQL (GQL) response into the hierarchical data structure. Passing this data into BlockRenderer component generates the WordPress content by matching the appropriate blocks and using a default block when block implementation is missing. Default block is also customizable component. Example also gives an option to provide custom HTML parser to render HTML content.

## Features

- 16 sample block components
- Custom HTML parser option
- Default block for fallback
- Custom default block option
- Utility to form inline CSS
- Utility to convert flat GQL data into hierarchical data
- GraphQL fragments for each block

## Important notes

- This example does not require a working WordPress instance, it uses a fake request to get a prefetched GQL response
- If you have a working WordPress instance you can easily connect it to this example. For the details check the `pages/index.js` file.
- You will need to have [`wp-graphql-content-blocks`](https://github.com/wpengine/wp-graphql-content-blocks) and [`WPGraphQL`](https://wordpress.org/plugins/wp-graphql/) plugins installed if you want to connect it to a WordPress instance.

## Project structure

```
/
├── public/
│   ├── post.json                       # Example GraphQL post data
├── pages/
│   ├── index.js                        # Home page to showcase all blocks
├── components/
│   ├── Caption.js                      # Component for figcaption and caption elements
│   ├── DefaultBlock.js                 # Default block to render if there's no corresponding block
│   ├── BlockRenderer.js                # Component to render to render blocks
│   ├── blocks/
│   │   ├── CoreAudio.js                # Audio block component
│   │   ├── CoreButton.js               # Button block component
│   │   └── ...                         # Other block components
├── utils/
│   ├── flatListToHierarchical.js       # Convert GraphQL data into hierarchical
│   ├── getInlineStyles.js              # Parse inline styles
├── package.json                        # Project dependencies and scripts
```

## Installation and Setup

### Prerequisites

1. Node.js 18.18 or later
2. npm or another package manager

### Clone the repository

```bash
git clone https://github.com/wpengine/hwptoolkit.git
cd examples/next/render-blocks-pages-router
```

### Install dependencies

```bash
npm install
```

### Start the development server

```bash
npm run dev
```

http://localhost:3000/ should render the blocks as shown below.

![Animated screenshot, scrolling down and revealing the blocks supported by this example rendered on the page.](./public/screenshot.gif)
