import { useGraphQL, gql, fetchGraphQL } from "./client";
import { useRuntimeConfig } from "nuxt/app";
// Format WordPress URL
export function formatWordPressUrl(uri) {
  if (!uri) return "/";

  // Remove the leading slash if present
  let cleanUri = uri.startsWith("/") ? uri.substring(1) : uri;

  // Remove trailing slash if present (except for root)
  cleanUri =
    cleanUri.endsWith("/") && cleanUri !== "/"
      ? cleanUri.slice(0, -1)
      : cleanUri;

  return `/${cleanUri}`;
}

export function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

export function capitalizeWords(str) {
  if (!str) return "";
  return str
    .split("-")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
}

export async function getPosts({
  query,
  slug = "",
  pageSize = 10,
  after = null,
  revalidate = false,
}) {
  if (!slug) {
    return await fetchGraphQL(query, {
      first: pageSize,
      after,
    });
  }

  const querySlug = typeof slug === "string" ? slug : String(slug);
  return await fetchGraphQL(query, {
    slug: querySlug,
    first: pageSize,
    after,
    revalidate,
  });
}

export function createExcerpt(content, length = 150) {
  if (!content) return "";
  return (
    content.replace(/<[^>]*>/g, "").substring(0, length) +
    (content.length > length ? "..." : "")
  );
}

// Define the query using gql


// Simple in-memory cache
let cachedPostsPerPage = 10; // Default value

// Simple function to get posts per page
export async function getPostsPerPage() {
  try {
    // If we've already fetched the value, return it
    if (cachedPostsPerPage !== 10) {
      return cachedPostsPerPage;
    }

    // Otherwise fetch from server
    const config = useRuntimeConfig();
    const wpUrl = config.public.wordpressUrl;
    const response = await fetch(`${wpUrl}/graphql`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        query: POSTS_PER_PAGE_QUERY, // Use the gql query directly
        variables: {},
      }),
    });

    if (!response.ok) return cachedPostsPerPage;

    const result = await response.json();
    const serverValue = parseInt(
      result.data?.allSettings?.readingSettingsPostsPerPage,
      10
    );

    if (!isNaN(serverValue)) {
      // Update cache with server value
      cachedPostsPerPage = serverValue;
    }

    return cachedPostsPerPage;
  } catch (error) {
    return cachedPostsPerPage; // Return default if anything fails
  }
}
// Fetch the data
export async function blogPostsPerPage() {
  const POSTS_PER_PAGE_QUERY = gql`
  query GetPostsPerPage {
    allSettings {
      readingSettingsPostsPerPage
    }
  }
`;
  const { data } = useGraphQL(POSTS_PER_PAGE_QUERY);
  const blogPostsPerPage = computed(
    () => data.value?.allSettings?.readingSettingsPostsPerPage
  );
  return Promise.resolve(blogPostsPerPage);
}
// Synchronous version - just returns whatever is cached
export function getPostsPerPageSync() {
  return cachedPostsPerPage;
}

// Simple initialization
export async function initializePostsPerPageCache() {
  try {
    const value = await getPostsPerPage();
    cachedPostsPerPage = value;
    return value;
  } catch (error) {
    return cachedPostsPerPage;
  }
}
export function flatListToHierarchical(
  data = [],
  { idKey = "id", parentKey = "parentId", childrenKey = "children" } = {}
) {
  const tree = [];
  const childrenOf = {};

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
