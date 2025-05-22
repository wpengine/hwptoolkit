<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Query to fetch basic post data
const HOME_QUERY = gql`
  query homeTemplatePostQuery {
    posts(first: 6, where: {status: PUBLISH}) {
      nodes {
        id
        title
        uri
        excerpt
        date
      }
    }
  }
`;

// Fetch the data
const { data, loading, error } = useGraphQL(HOME_QUERY);

// Format WordPress URL helper
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

// Computed properties with better fallbacks
const posts = computed(() => data.value?.posts?.nodes || []);
</script>

<template>
  <main id="home" class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold mb-4">My WP + Nuxt Blog!</h1>

    <p class="text-xl mb-12">I like sharing my life!</p>

    <section id="recent-posts" class="mb-8">
      <h2 class="text-2xl font-semibold mb-6">Recent Posts</h2>
      
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
          <div class="post-excerpt mb-4 text-gray-600" v-html="post.excerpt"></div>
          <NuxtLink 
            class="post-link text-blue-600 hover:underline" 
            :to="formatWordPressUrl(post.uri)"
          >
            Read more...
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
/* Add any component-specific styling here */
.post-excerpt :deep(p) {
  margin-bottom: 0.75rem;
}
</style>