<script setup>
import { useRoute } from 'vue-router';
import { ref, computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Import your template components
import HomeTemplate from '../components/templates/Home.vue'; 
import BlogTemplate from '../components/templates/Blog.vue'; 
import PostTemplate from '../components/templates/single/Post.vue';
import PageTemplate from '../components/templates/single/Page.vue';
import NotFoundTemplate from '../components/templates/404.vue';

const route = useRoute();
const slug = computed(() => {
  if (Array.isArray(route.params.slug)) {
    return route.params.slug.join('/');
  }
  return route.params.slug || '/'; // Default to '/' if undefined
});

// Special case for homepage
const isHomePage = computed(() => {
  return !slug.value || slug.value === '/' || slug.value === 'index';
});

// Special case for blog listing
const isBlogListing = computed(() => {
  return slug.value === 'blog' || slug.value === 'blog/';
});

// Blog pagination detection
const blogPageMatch = computed(() => {
  if (!slug.value) return null;
  const match = slug.value.match(/^blog\/page\/(\d+)$/);
  return match ? parseInt(match[1], 10) : null;
});

const isBlogPagination = computed(() => {
  return !!blogPageMatch.value;
});

// Update blog listing check to include pagination
const isBlogPage = computed(() => {
  return isBlogListing.value || isBlogPagination.value;
});

// Skip content type query for homepage and blog pages
const skipContentTypeQuery = computed(() => isHomePage.value || isBlogPage.value);

// Get the current blog page from the URL
const blogPage = computed(() => {
  return blogPageMatch.value || 1;
});

// Query to determine content type AND fetch basic data
const CONTENT_TYPE_QUERY = gql`
  query GetContentType($slug: ID!) {
    # Try to fetch as a post
    post(id: $slug, idType: SLUG) {
      id
      databaseId
      title
      contentType {
        node {
          name
        }
      }
    }
    # Try to fetch as a page
    page(id: $slug, idType: URI) {
      id
      databaseId
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
  { slug: slug.value },
  { enabled: !skipContentTypeQuery.value }
);

// Determine content type
const contentType = computed(() => {
  if (isHomePage.value) return 'home';
  if (isBlogPage.value) return 'blog';
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
    typeData: typeData.value,
    isHomePage: isHomePage.value,
    isBlogPage: isBlogPage.value,
    blogPage: blogPage.value
  };
});
</script>

<template>
  <div>
    <!-- Loading state - skip for home and blog pages -->
    <div v-if="typeLoading && !isHomePage && !isBlogPage" class="container mx-auto p-4 max-w-3xl py-10 text-center">
      <p>Loading content...</p>
    </div>
    
    <!-- Error state - skip for home and blog pages -->
    <div v-else-if="typeError && !isHomePage && !isBlogPage" class="container mx-auto p-4 max-w-3xl py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ typeError.message }}</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
    
    <!-- Content Type Dispatcher -->
    <template v-else>
      <!-- Homepage -->
      <HomeTemplate v-if="contentType === 'home'" />
      
      <!-- Blog listing - pass the page number for pagination -->
      <BlogTemplate v-else-if="contentType === 'blog'" :page="blogPage" />
      
      <!-- Post template -->
      <PostTemplate v-else-if="contentType === 'post'" :slug="slug" />
      
      <!-- Page template -->
      <PageTemplate v-else-if="contentType === 'page'" :slug="slug" />
      
      <!-- Not found template -->
      <NotFoundTemplate v-else />
    </template>
  </div>
</template>