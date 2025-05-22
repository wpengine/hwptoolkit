<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../../lib/client';

const props = defineProps({
  slug: {
    type: String,
    required: true
  }
});

const PAGE_QUERY = gql`
  query GetPage($slug: ID!) {
    page(id: $slug, idType: URI) {
      id
      title
      content
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
    }
  }
`;

const { data, loading, error } = useGraphQL(PAGE_QUERY, { slug: props.slug });
const page = computed(() => data.value?.page || null);
</script>

<template>
  <div class="container mx-auto p-4 max-w-4xl">
    <!-- Loading state -->
    <div v-if="loading" class="py-10 text-center">
      <p>Loading page...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ error.message }}</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
    
    <!-- Page content -->
    <article v-else-if="page" class="py-10">
      <h1 class="text-4xl font-bold mb-8 text-center">{{ page.title }}</h1>
      
      <!-- Featured image -->
      <img 
        v-if="page.featuredImage?.node" 
        :src="page.featuredImage.node.sourceUrl" 
        :alt="page.featuredImage.node.altText || page.title"
        class="w-full h-auto rounded-lg mb-8 shadow-md"
      />
      
      <!-- Page content -->
      <div class="prose prose-lg max-w-none" v-html="page.content"></div>
    </article>
    
    <!-- Not found state -->
    <div v-else class="py-10 text-center">
      <h1 class="text-2xl font-bold mb-2">Page Not Found</h1>
      <p>The page you're looking for doesn't exist or has been removed.</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>
/* Add page-specific styling here */
:deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 1.5rem 0;
}

:deep(a) {
  color: #3b82f6;
  text-decoration: none;
}

:deep(a:hover) {
  text-decoration: underline;
}
</style>