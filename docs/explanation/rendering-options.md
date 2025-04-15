# Headless WordPress Rendering Options
## Introduction

This document explores the various approaches to rendering content from a headless WordPress installation. As a front-end developer working with headless WordPress, you'll need to make important decisions about how to handle and display WordPress content in your frontend application. This guide aims to help you understand the available options, their trade-offs, and best practices.

### 1. Rendering Raw HTML Content from WordPress Classic Editor
When working with content created in the WordPress Classic Editor, you typically receive raw HTML content that needs to be rendered in your front-end application. Here's an overview of how to handle this content, focusing on React as an example framework.

#### Overview
* **Raw HTML Content**: Content from the WordPress Classic Editor is often in raw HTML format, which includes tags, classes, and inline styles.

* **Front-end Rendering**: To display this content in a React application, you need to render the raw HTML safely and efficiently.

#### Implementation
To render raw HTML in React, you can use the `dangerouslySetInnerHTML` property. Here's a basic example for a single post:

```javascript
const query = gql`query GetPost {
  post(id: "hello-world", idType: SLUG) {
    content
  }
}`;

function PostContent({ content }) {
  return <div dangerouslySetInnerHTML={{ __html: content }} />;
}
```
**Note**: Using `dangerouslySetInnerHTML` poses security risks if the content isn't sanitized.

#### Considerations:
* Simplicity: Easy to implement initially with dangerouslySetInnerHTML (React) or v-html (Vue).
* Styling Challenges: Global CSS styles needed to match WordPress styling which is usually not available
* Limited Control: Limited ability to manipulate or enhance specific content elements.

Considering those limitations, it's clear that rendering raw HTML Content is not suitable for most use cases. Hopefully there are better alternatives.

### 2. Rendering Block Editor (Gutenberg) Content
#### Overview
WordPress Block Editor (Gutenberg) stores content as HTML with special comments and structured JSON properties in the comments representing blocks, offering more flexibility for headless implementations. However exposing them in a Headless enviroment is tricky. Thats why with the help of the [wp-graphql-content-blocks](https://github.com/wpengine/wp-graphql-content-blocks) plugin, you can query (Gutenberg) Blocks as data using graphql.

This gives you more controls and ability to manipulate or enhance specific content elements or blocks compared to the previous approach. Here is how to do it:

1. Querying the block data

```javascript
const query = gql`query GetPostBlocks {
  posts {
    nodes {
      editorBlocks {
        __typename
        name
        ... on CoreParagraph {
          attributes {
            content
          }
        }
      }
    }
  }
}`;
```
The response would include the block's `__typename`, `name`, and the `content` attribute:

```json
{
  "__typename": "CoreParagraph",
  "name": "core/paragraph",
  "attributes": {
    "content": "Hello World"
  }
}
```
This query fetches the content of a `CoreParagraph` block, allowing you to access and manipulate its text content in your application.

To render this content in a React application, you can use the `dangerouslySetInnerHTML` property, but ensure you sanitize the content first to prevent XSS attacks:

```javascript
import DOMPurify from 'dompurify';

function PostContent({ attributes }) {
  const { content } = attributes;
  const sanitizedContent = DOMPurify.sanitize(content);
  return <div dangerouslySetInnerHTML={{ __html: sanitizedContent }} />;
}
```
Similar to the Classic Editor approach, you can render the final HTML output of Gutenberg blocks.

#### Considerations:
* Querying: The ability to query block data using GraphQL provides a structured approach to accessing and manipulating content.
* Complexity: Handling nested blocks and reconstructing their hierarchy can be complex.
* Styling: While able to query classNames or block attributes, developers still have to provide the actual styles for the components to display correctly.

### 3. Rendering Blocks with WordPress Styles

Rendering Gutenberg blocks with WordPress styles in a headless environment involves fetching and applying the styles defined in WordPress to your frontend application. This approach ensures that the content looks consistent with how it appears in the WordPress editor.

#### Challenges
* CSS Sources: Gutenberg block styles come from multiple sources, including core blocks, themes, and user-defined styles. This complexity makes it challenging to replicate the exact styling in a headless setup.

* Inline Styles and CSS Variables: Gutenberg blocks often include inline styles and CSS variables (e.g., `var(--wp--preset--color--cyan-bluish-gray))`. These styles are not automatically applied when rendering blocks in a headless environment.

* Theme Styles: Themes provide additional styles that are crucial for maintaining consistency. However, fetching these styles dynamically can be difficult, especially if they are generated inline or through WordPress's Global Styles feature.

#### Solutions
* Import Global Stylesheet: Use tools like faust-cli to generate and import a global stylesheet from your WordPress site. This stylesheet includes CSS variables and other theme-specific styles.

```bash
"scripts": {
  "generate": "faust generatePossibleTypes && faust generateGlobalStylesheet",
}
```
Then import the generated stylesheet in your application:

```javascript
import "../globalStylesheet.css";
```

* Include Block Library Styles: Import CSS styles from `@wordpress/block-library` to apply basic block styling:

```javascript
import "@wordpress/block-library/build-style/common.css";
import "@wordpress/block-library/build-style/style.css";
import "@wordpress/block-library/build-style/theme.css";
```

**Note**: Changes to the `@wordpress/block-library` package may introduce new styles or css classnames potentially changing the look and feel of the application.

* Define Custom CSS Variables: If using CSS variables, define them manually in your application's CSS to match WordPress's presets:

```css
:root {
  --wp--preset--color--black: #000000;
  --wp--preset--color--cyan-bluish-gray: #abb8c3;
}
```
Considerations
* Styling Parity: Achieving perfect styling parity can be challenging due to the dynamic nature of WordPress styles.

* Maintenance: Styles may change with theme updates, customizations or major WordPress updates, requiring periodic updates in your headless application.
