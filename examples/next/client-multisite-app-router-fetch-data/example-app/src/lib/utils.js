import getConfig from "next/config";
import { fetchGraphQL } from "../lib/client";

export function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
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
  siteKey,
  slug = "",
  pageSize = 10,
  after = null,
  revalidate = false,
}) {
  if (!slug) {
    return await fetchGraphQL(query, siteKey, {
      first: pageSize,
      after,
    });
  }

  const querySlug = typeof slug === "string" ? slug : String(slug);
  return await fetchGraphQL(query, siteKey, {
    slug: querySlug,
    first: pageSize,
    after,
    revalidate,
  });
}

export function getPostsPerPage() {
  const { publicRuntimeConfig } = getConfig() || {};
  return publicRuntimeConfig?.wordPressDisplaySettings?.postsPerPage || 10;
}

export function createExcerpt(content, length = 150) {
  if (!content) return "";
  return (
    content.replace(/<[^>]*>/g, "").substring(0, length) +
    (content.length > length ? "..." : "")
  );
}
