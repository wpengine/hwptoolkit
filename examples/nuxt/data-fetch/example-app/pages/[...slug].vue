<script setup>
import { useRoute } from 'vue-router';
import { ref, computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Import your template components
import PostTemplate from '../components/templates/single/Post.vue';
import PageTemplate from '../components/templates/single/Page.vue';
import NotFoundTemplate from '../components/templates/404.vue';

const route = useRoute();
const slug = computed(() => {
  if (Array.isArray(route.params.slug)) {
    return route.params.slug.join('/');
  }
  return route.params.slug;
});

// Query to determine content type AND fetch basic data
const CONTENT_TYPE_QUERY = gql`
  query GetContentType($slug: ID!) {
    # Try to fetch as a post
    post(id: $slug, idType: SLUG) {
      id
      title
      content
      contentType {
        node {
          name
        }
      }
    }
    # Try to fetch as a page
    page(id: $slug, idType: URI) {
      id
      title
      contentType {
        node {
          name
        }
      }
    }
  }
`;

// First determine what type of content this is
const { data: typeData, loading: typeLoading, error: typeError } = useGraphQL(
  CONTENT_TYPE_QUERY, 
  { slug: slug.value }
);

// Determine content type
const contentType = computed(() => {
  if (!typeData.value) return null;
  
  if (typeData.value.post) return 'post';
  if (typeData.value.page) return 'page';
  
  return null; // Not found
});

// Debugging info
const debugInfo = computed(() => {
  return {
    slug: slug.value,
    contentType: contentType.value,
    typeData: typeData.value
  };
});
</script>

<template>
  <div>
    <!-- Loading state -->
    <div v-if="typeLoading" class="container mx-auto p-4 max-w-3xl py-10 text-center">
      <p>Loading content...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="typeError" class="container mx-auto p-4 max-w-3xl py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ typeError.message }}</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
    
    <!-- Content Type Dispatcher -->
    <template v-else>
      <!-- Post template -->
      <PostTemplate v-if="contentType === 'post'" :slug="slug" />
      
      <!-- Page template -->
      <PageTemplate v-else-if="contentType === 'page'" :slug="slug" />
      
      <!-- Not found template -->
      <NotFoundTemplate v-else />
    
    </template>
  </div>
</template>