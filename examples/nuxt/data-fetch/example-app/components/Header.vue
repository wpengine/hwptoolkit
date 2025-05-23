<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';
import { useRoute } from 'vue-router';
import NavigationItem from './templates/header/NavigationItem.vue';
import { flatListToHierarchical } from '../lib/graphql';

// Get current route for active menu item
const route = useRoute();

// Query to fetch both site info and menu
const HEADER_QUERY = gql`
  query headerNavQuery($after: String = null) {
    generalSettings {
      title
    }
    menu(id: "primary", idType: LOCATION) {
      menuItems(first: 100, after: $after) {
        pageInfo {
          hasNextPage
          endCursor
        }
        nodes {
          id
          label
          uri
          parentId
        }
      }
    }
  }
`;

// Fetch the data
const { data, loading, error } = useGraphQL(HEADER_QUERY);

// Computed properties for better template readability
const siteTitle = computed(() => data.value?.generalSettings?.title || 'My WordPress Site');

// Get flat menu items
const flatMenuItems = computed(() => data.value?.menu?.menuItems?.nodes || []);

// Convert flat menu to hierarchical structure
const menuItems = computed(() => {
  return flatListToHierarchical(flatMenuItems.value);
});

// Determine if a menu item is active
const isActive = (item) => {
  if (!item.uri) return false;

  // Format the URI for comparison
  let cleanUri = item.uri.startsWith('/') ? item.uri.substring(1) : item.uri;
  cleanUri = cleanUri.endsWith('/') ? cleanUri.slice(0, -1) : cleanUri;

  // Compare with current route path
  return `/${cleanUri}` === route.path;
};
</script>

<template>
  <header class="header">
    <div class="main-header-wrapper">
      <div class="site-title-wrapper">
        <NuxtLink to="/">
          {{ siteTitle }}
        </NuxtLink>
      </div>
      <nav class="nav">
        <template v-if="!loading && !error">
          <!-- Dynamic menu items from WordPress using NavigationItem -->
          <NavigationItem v-for="item in menuItems" :key="item.id" :item="item" :is-active="isActive(item)" />
        </template>
      </nav>
    </div>
  </header>
</template>
<style scoped>
.header {
  background-color: #f8f9fa;
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}
.main-header-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
}
.nav {
  display: flex;
  gap: 1.5rem;
}
</style>