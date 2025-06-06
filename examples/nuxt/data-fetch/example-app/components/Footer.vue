<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Query to fetch footer data
const FOOTER_QUERY = gql`
  query GetFooterData {
    generalSettings {
      title
    }        
  }
`;

const { data } = useGraphQL(FOOTER_QUERY);

const siteTitle = computed(() => data.value?.generalSettings?.title || 'My WordPress Site');
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="footer">
      <div class="footer-content">
        <p>© {{ currentYear }} <NuxtLink to="/">{{ siteTitle }}</NuxtLink>. All rights reserved.</p>
        <p>Built with WordPress, Nuxt, and WPGraphQL.</p>
      </div>  
  </footer>
</template>

<style scoped>
.footer {
  border-top: 1px solid #e5e7eb;
  color: #333;
  margin: 2rem 0 0 0;
  padding-top: 2rem;
  padding-bottom: 1.5rem; 
}
.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem; 
  text-align: center; 
}
</style>