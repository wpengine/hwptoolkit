<script setup>
import { computed, ref } from 'vue';
import { useGraphQL, fetchGraphQL, gql } from '../../lib/client';
import { capitalizeWords, getPosts } from '../../lib/utils';
import PostListing from '../templates/listing/Post.vue';
import BlogClient from '../blog/Blog.client.vue';

const props = defineProps({
  category: {
    type: String,
    default: ''
  },
  tag: {
    type: String,
    default: ''
  },
  titlePrefix: {
    type: String,
    default: 'Blog'
  }
});

// Get the slug based on category or tag
const slug = props.category || props.tag || '';

// Format title
const pageTitle = computed(() => {
  const capitalizedSlug = capitalizeWords(slug);

  if (capitalizedSlug) {
    return `${props.titlePrefix}: ${capitalizedSlug}`;
  }

  return props.titlePrefix;
});

const POSTS_QUERY = gql`
  query GetPosts($first: Int = 9, $after: String, $category: String, $tag: String) {
    posts(
      first: $first, 
      after: $after, 
      where: {
        categoryName: $category,
        tag: $tag
      }
    ) {
      pageInfo {
        hasNextPage
        endCursor
      }
      edges {
        cursor
        node {
          id
          title
          date
          excerpt
          uri
          slug
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
          categories {
            nodes {
              name
              slug
            }
          }
          author {
            node {
              name
              avatar {
                url
              }
            }
          }
        }
      }
    }
  }
`;

const postsPerPage = 6; // Fetch 5 posts initially
// Track loading state
const loading = ref(true);
const error = ref(null);

// Fetch initial posts
let data;

try {
  data = await getPosts({
    query: POSTS_QUERY,
    slug,
    pageSize: postsPerPage,
    revalidate: 3600, // Caches for 60 minutes
  });
  loading.value = false;
} catch (err) {
  error.value = err;
  loading.value = false;
  data = null;
}

// Extract initial posts and pageInfo
const allPosts = ref(data?.posts?.edges?.map(edge => edge.node) || []);
const initialPageInfo = data?.posts?.pageInfo || {};

// Handle new posts from client component
const handleNewPosts = (newPosts) => {
  allPosts.value = [...allPosts.value, ...newPosts];
};

// Handle loading state
const handleLoading = (isLoading) => {
  loading.value = isLoading;
};
</script>

<template>
  <div class="container mx-auto p-4 max-w-3xl">
    <header>
      <h1>{{ pageTitle }}</h1>
    </header>

    <!-- Error state -->
    <div v-if="error">
      <h2>Error</h2>
      <p>{{ error.message || 'An error occurred while loading posts' }}</p>
    </div>

    <!-- Loading state -->
    <div v-else-if="loading && allPosts.length === 0">
      <p>Loading posts...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="allPosts.length === 0">
      <p>No posts found.</p>
    </div>

    <!-- Initial SSR and client-side component for pagination -->
    <template v-else>
      <!-- All posts rendered in a single component -->
      <PostListing :posts="allPosts" :loading="loading" :cols="3" />

      <!-- Client component for pagination (only button) -->
      <BlogClient :initial-posts="allPosts" :initial-page-info="initialPageInfo" :posts-per-page="postsPerPage"
        :posts-query="POSTS_QUERY" :category="props.category" :tag="props.tag" @update:posts="handleNewPosts"
        @loading="handleLoading" />
    </template>
  </div>
</template>