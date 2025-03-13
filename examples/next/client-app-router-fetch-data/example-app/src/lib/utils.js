import nextConfig from '../../next.config.mjs';
import { fetchGraphQL } from '../lib/client';


export function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

export function capitalizeWords(str) {
  if (!str) return '';
  return str
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

export async function getPosts({ query, slug = '', pageSize = 10, after = null, revalidate = false }) {

    if (! slug) {
      console.log(query, pageSize, after);
      return await fetchGraphQL(query, {
        first: pageSize,
        after
      });
    }

    const querySlug = typeof slug === 'string' ? slug : String(slug);
    return await fetchGraphQL(query, {
      slug: querySlug,
      first: pageSize,
      after,
      revalidate
    });
}

export function getPostsPerPage() {
  return nextConfig.wordPressDisplaySettings?.postsPerPage || 10;
}
