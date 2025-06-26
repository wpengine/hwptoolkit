<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';
import { useRoute } from 'vue-router';
import NavigationItem from './templates/header/NavigationItem.vue';
import { flatListToHierarchical } from '../lib/utils';

// Get current route for active menu item
const route = useRoute();

const SETTINGS_QUERY = gql`
  query HeaderSettingsQuery {
    generalSettings {
      title
    }
  }
`;

const NAVIGATION_QUERY = gql`
  query HeaderNavigationQuery($after: String = null) {
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
          target
          cssClasses
          title
          description
        }
      }
    }
  }
`;

// Use unique keys for proper SSR state management
const {
  data: settingsData,
  loading: settingsLoading,
  error: settingsError
} = useGraphQL(SETTINGS_QUERY, {}, {
  key: 'header-settings',
  loadingText: 'Loading site title...'
});

const {
  data: navigationData,
} = useGraphQL(NAVIGATION_QUERY, {}, {
  key: 'header-navigation',
  loadingText: 'Loading navigation...'
});


const siteInfo = computed(() => {
  const title = settingsData.value?.generalSettings?.title;

  return {
    title: title
  };
});

// Get flat menu items
const flatMenuItems = computed(() => {
  return navigationData.value?.menu?.menuItems?.nodes || [];
});

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
  <header class="header" v-if="!settingsLoading">
    <div class="main-header-wrapper">
      <div class="site-title-wrapper">
        <NuxtLink to="/">
          <template v-if="siteInfo.title">
            {{ siteInfo.title }}
          </template>
        </NuxtLink>
      </div>

      <nav class="nav">
        <!-- Navigation items -->
        <template v-if="menuItems.length > 0">
          <NavigationItem v-for="item in menuItems" :key="item.id" :item="item" :is-active="isActive(item)" />
        </template>
      </nav>
    </div>
  </header>
</template>

<style scoped lang="scss">
@use '@/assets/scss/components/header/header';
</style>