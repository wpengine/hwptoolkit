<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../lib/client';

// Define props that will be passed from the page loader
const props = defineProps({
  graphqlData: {
    type: Object,
    default: () => ({})
  },
  slug: String,
  params: Object,
  query: Object,
  template: Object
});
const archiveQuery = {
    query: gql`
      query ArchiveTemplateNodeQuery($uri: String!) {
        archive: nodeByUri(uri: $uri) {
          __typename
          ... on User {
            name
            description
            contentNodes: posts {
              nodes {
                id
                uri
                title
                excerpt
              }
            }
          }
          ... on TermNode {
            name
            description
          }
          ... on Tag {
            name
            description
            contentNodes {
              nodes {
                id
                ... on NodeWithTitle {
                  title
                }
                ... on NodeWithExcerpt {
                  excerpt
                }
                uri
              }
            }
          }
          ... on Category {
            name
            description
            contentNodes {
              nodes {
                id
                ... on NodeWithTitle {
                  title
                }
                ... on NodeWithExcerpt {
                  excerpt
                }
                uri
              }
            }
          }
        
      }
    `
};
const uri = useRoute().path || '/'
const { archiveData, loading, error } = useGraphQL(archiveQuery, { slug: uri });

// Computed properties for template data
const archive = computed(() => archiveData.value);
const contentNodes = computed(() => archive.value?.contentNodes?.nodes || []);
</script>

<template>
  <main class="container mx-auto p-4 max-w-3xl">
    <!-- Loading state -->
    <div v-if="loading" class="py-10 text-center">
      <p>Loading archive...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ error.message }}</p>
    </div>
    
    <!-- Archive content -->
    <div v-else-if="archive">
      <header class="mb-8">
        <h1 class="text-4xl font-bold mb-4">{{ archive.name }}</h1>
        <div v-if="archive.description" class="text-gray-600" v-html="archive.description"></div>
      </header>
      
      <!-- Content list -->
      <section v-if="contentNodes.length > 0">
        <ol class="space-y-6">
          <li v-for="content in contentNodes" :key="content.id" class="border-b border-gray-200 pb-4">
            <article>
              <h2 class="text-xl font-semibold mb-2">
                <NuxtLink 
                  :to="content.uri" 
                  class="text-blue-600 hover:text-blue-800 hover:underline"
                  v-html="content.title"
                />
              </h2>
              
              <div v-if="content.excerpt" class="excerpt text-gray-600" v-html="content.excerpt"></div>
            </article>
          </li>
        </ol>
      </section>
      
      <!-- Empty state -->
      <div v-else class="py-10 text-center text-gray-500">
        <p>No content found in this archive.</p>
      </div>
    </div>
    
    <!-- Fallback -->
    <div v-else class="py-10 text-center">
      <h1 class="text-2xl font-bold mb-2">Archive Not Found</h1>
      <p>The requested archive could not be found.</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return home
      </NuxtLink>
    </div>
  </main>
</template>

<style scoped>
.excerpt :deep(p) {
  margin: 0;
}

.excerpt :deep(a) {
  color: #3b82f6;
  text-decoration: underline;
}
</style>

<script>
// Export queries that will be used by the page loader - equivalent to SvelteKit's module script

</script>