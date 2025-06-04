<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../../lib/client';
import Comments from '../../Comments.vue';

const props = defineProps({
  slug: {
    type: String,
    required: true
  }
});

const PAGE_QUERY = gql`
  query GetPage($slug: ID!) {
    page(id: $slug, idType: SLUG) {
      id
      databaseId
      title
      content
      commentCount
      commentStatus
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
const pageId = computed(() => page.value?.databaseId || null);

// Check if comments are enabled for this page
const commentsEnabled = computed(() => 
  page.value?.commentStatus === 'open' || // 'open' means comments are enabled
  page.value?.commentCount > 0 // Show section if there are existing comments
);
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
      
      <!-- Comments section - only show if comments are enabled -->
      <Comments 
        v-if="commentsEnabled && pageId" 
        :post-id="pageId" 
        content-type="page"
      />
      
      <!-- Message if comments are closed but exist -->
      <div 
        v-else-if="page.commentCount > 0 && page.commentStatus !== 'open'" 
        class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-500"
      >
        <p>Comments are closed for this page.</p>
      </div>
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