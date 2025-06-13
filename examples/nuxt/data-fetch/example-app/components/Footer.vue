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

const { 
  data: settingsData, 
} = useGraphQL(FOOTER_QUERY, {}, { 
  key: 'header-settings',
  loadingText: 'Loading site title...' 
});

const siteInfo = computed(() => {
  const title = settingsData.value?.generalSettings?.title;
  
  return {
    title: title
  };
});
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="footer">
      <div class="footer-content">
        <p>© {{ currentYear }} <NuxtLink to="/">{{ siteInfo.title }}</NuxtLink>. All rights reserved.</p>
        <p>Built with WordPress, Nuxt, and WPGraphQL.</p>
      </div>  
  </footer>
</template>

<style scoped lang="scss">
@use '@/assets/scss/components/footer';
</style>