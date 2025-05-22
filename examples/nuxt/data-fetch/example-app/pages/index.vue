<script setup>
import { computed, ref } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Query to fetch both site info and posts
const HOME_QUERY = gql`
  query HomePageQuery {
    generalSettings {
      title
      description
    }
    posts(first: 6, where: {status: PUBLISH}) {
      nodes {
        id
        title
        date
        uri
        slug
        excerpt
      }
    }
  }
`;

// Fetch the data
const { data, loading, error } = useGraphQL(HOME_QUERY);

// Computed properties with better fallbacks
const posts = computed(() => data.value?.posts?.nodes || []);
const siteInfo = computed(() => ({
  title: data.value?.generalSettings?.title || 'My WordPress Site',
  description: data.value?.generalSettings?.description || 'Welcome to my site'
}));

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

// Improved URL formatter for WordPress URLs
const formatWordPressUrl = (uri) => {
  if (!uri) return '/';
  
  // Remove the leading slash if present
  let cleanUri = uri.startsWith('/') ? uri.substring(1) : uri;
  
  // Remove trailing slash if present (except for root)
  cleanUri = cleanUri.endsWith('/') && cleanUri !== '/' 
    ? cleanUri.slice(0, -1) 
    : cleanUri;
    
  return `/${cleanUri}`;
};

// If we can't find posts with URI, we'll use slug as fallback
const getPostLink = (post) => {
  if (post.uri) return formatWordPressUrl(post.uri);
  if (post.slug) return `/posts/${post.slug}`;
  return '/';
};
</script>

<template>
  <main class="container mx-auto p-4 max-w-3xl">
    <!-- Site Info -->
    <header class="text-center mb-12 py-8">
      <div v-if="loading">Loading site information...</div>
      <div v-else-if="error">Error loading data</div>
      <div v-else>
        <h1 class="text-4xl font-bold mb-4">{{ siteInfo.title }}</h1>
        <p class="text-xl text-gray-600">{{ siteInfo.description }}</p>
      </div>
    </header>

    <!-- Recent Posts -->
    <section id="recent-posts" class="mb-8">
      <h2 class="text-2xl font-bold mb-6">Recent Posts</h2>
      
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-8">
        <p>Loading posts...</p>
      </div>
      
      <!-- Error state -->
      <div v-else-if="error" class="text-center py-8 text-red-500">
        <p>Failed to load posts</p>
        <p class="text-sm">{{ error.message }}</p>
      </div>
      
      <!-- Posts grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="post in posts" 
          :key="post.id" 
          class="post border rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow"
        >
          <h3 class="post-title text-xl font-medium mb-3">{{ post.title }}</h3>
          <time class="text-sm text-gray-500 block mb-2">{{ formatDate(post.date) }}</time>
          <div class="post-excerpt mb-4 text-gray-600" v-html="post.excerpt"></div>
          <NuxtLink 
            class="post-link text-blue-600 hover:underline" 
            :to="formatWordPressUrl(post.uri)"
          >
            Read more →
          </NuxtLink>
        </div>
      </div>
      
      <!-- Empty state -->
      <div v-if="posts.length === 0 && !loading && !error" class="text-center py-8 text-gray-500">
        <p>No posts found</p>
      </div>
    </section>
  </main>
</template>

<style scoped>
/* Handle WP content styling */
.post-excerpt :deep(p) {
  margin-bottom: 0.75rem;
}
</style>