import { fetchGraphQL } from './graphql.service';

/**
 * Fetches posts from the GraphQL API.
 * This function abstracts the GraphQL query execution and handles pagination.
 * It can be used to fetch posts by category, tag, or any other criteria defined in the GraphQL query.
 * @param {Object} params - The parameters for fetching posts
 * @param {string} params.query - The GraphQL query to execute
 * @param {string} [params.slug=""] - The slug for filtering posts (e.g., category or tag slug)
 * @param {number} [params.pageSize=10] - The number of posts to fetch per page
 * @param {string|null} [params.after=null] - The cursor for pagination (used for fetching the next page of results)
 * @param {boolean} [params.revalidate=false] - Whether to revalidate the cache for this request
 * @returns {Promise<any>} A promise that resolves to the fetched posts data.
 */
export async function getPosts({
  query,
  slug = '',
  pageSize = 10,
  after = null,
  revalidate = false,
}: {
  query: string;
  slug?: string;
  pageSize?: number;
  after?: string | null;
  revalidate?: boolean;
}) {
  if (!slug) {
    return await fetchGraphQL(query, {
      first: pageSize,
      after,
    });
  }

  const querySlug = typeof slug === 'string' ? slug : String(slug);
  return await fetchGraphQL(query, {
    slug: querySlug,
    first: pageSize,
    after,
    revalidate,
  });
}

/**
 * Formats a date string into a human-readable format.
 * Used for displaying post dates in a user-friendly way.
 * @param {string} dateString - The date string to format
 * @returns {string} The formatted date string in "Month Day, Year at HH:MM AM/PM" format
 * Return example: March 15, 2013 at 03:47 PM
 */
export function formatDate(dateString: string) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

/**
 * Generates a URL for a post category.
 *
 * @param {string} slug - The slug of the category
 * @returns {string} The URL for the post category
 */
export function getCategoryLink(slug: string) {
  return `/category/${slug}/`;
}

/**
 * Generates a URL for a post tag.
 *
 * @param {string} slug - The slug of the tag
 * @returns {string} The URL for the post tag
 */
export function getTagLink(slug: string) {
  return `/tag/${slug}/`;
}

/**
 * Limits the excerpt to a specified length and adds ellipsis if truncated.
 * Used for post listings.
 *
 * @param {string} content - The content to create an excerpt from
 * @param {number} [length=150] - The maximum length of the excerpt
 * @return {string} The excerpt text, truncated and with ellipsis if necessary
 */
export function createExcerpt(content: string, length = 150) {
  if (!content) return '';
  return (
    content.replace(/<[^>]*>/g, '').substring(0, length) +
    (content.length > length ? '...' : '')
  );
}

/**
 * Converts a flat list of items into a hierarchical tree structure.
 * Used in Header component for navigation menu items.
 *
 * @param data - The flat array of items to convert into a tree structure
 * @param options - Configuration options for the conversion
 * @param options.idKey - The property name that contains the unique identifier for each item (default: "id")
 * @param options.parentKey - The property name that contains the parent identifier for each item (default: "parentId")
 * @param options.childrenKey - The property name to use for storing children in the  tree structure (default: "children")
 * @returns A hierarchical tree structure where each item contains its children in the specified children property
 */
export function flatListToHierarchical(
  data: any[] = [],
  { idKey = 'id', parentKey = 'parentId', childrenKey = 'children' } = {}
) {
  const tree: any[] = [];
  const childrenOf: Record<string | number, any[]> = {};

  for (const item of data) {
    const newItem = { ...item };

    const id = newItem?.[idKey];
    const parentId = newItem?.[parentKey] ?? 0;

    if (!id) {
      continue;
    }

    childrenOf[id] = childrenOf[id] || [];
    newItem[childrenKey] = childrenOf[id];

    parentId
      ? (childrenOf[parentId] = childrenOf[parentId] || []).push(newItem)
      : tree.push(newItem);
  }
  return tree;
}

/**
 * Capitalizes the first letter of each word in a string and replaces hyphens with spaces.
 * @param str - The string to capitalize
 * @returns capitalized string with words separated by spaces
 */
export function capitalizeWords(str: string) {
  if (!str) return '';
  return str
    .split('-')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}
