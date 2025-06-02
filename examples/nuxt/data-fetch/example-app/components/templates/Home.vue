<script setup>
import { computed, ref } from 'vue';
import { useGraphQL, gql } from '../../lib/client';
import PostListing from './listing/Post.vue';

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
        featuredImage {
          node {
            sourceUrl
            altText
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
        categories {
          nodes {
            name
            slug
          }
        }
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
</script>

<template>
  <main>
    <!-- Site Info -->
    <header>
      <div v-if="loading && !data">Loading site information...</div>
      <div v-else-if="error">Error loading data</div>
      <div v-else>
        <h1>{{ siteInfo.title }}</h1>
        <p>{{ siteInfo.description }}</p>
      </div>
    </header>

    <!-- Recent Posts -->
    <section id="recent-posts">
      <h2>Recent Posts</h2>
      
      <!-- Error state -->
      <div v-if="error">
        <p>Failed to load posts</p>
        <p>{{ error.message }}</p>
      </div>
      
      <!-- Post listing component -->
      <PostListing 
        :posts="posts" 
        :loading="loading" 
      />
    </section>
    
    <!-- Blog Link -->
    <div>
      <NuxtLink to="/blog">
        View All Blog Posts →
      </NuxtLink>
    </div>
  </main>
</template>

<style scoped>
/* Add any component-specific styling here */
</style>