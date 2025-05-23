<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../lib/client';

// Query to fetch footer data
const FOOTER_QUERY = gql`
  query GetFooterData {
    generalSettings {
      title
    }
    menus(where: { location: FOOTER }) {
      nodes {
        menuItems {
          nodes {
            id
            path
            label
            uri
          }
        }
      }
    }
    sidebarWidgets(first: 3, where: { location: "footer" }) {
      nodes {
        id
        name
        widgets {
          id
          title
          content
        }
      }
    }
  }
`;

// Fetch the data
const { data, loading, error } = useGraphQL(FOOTER_QUERY);

// Computed properties
const siteTitle = computed(() => data.value?.generalSettings?.title || 'My WordPress Site');
const footerMenu = computed(() => data.value?.menus?.nodes?.[0]?.menuItems?.nodes || []);
const footerWidgets = computed(() => data.value?.sidebarWidgets?.nodes?.[0]?.widgets || []);

// Get current year for copyright
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="bg-gray-800 text-white mt-12 pt-8 pb-6">
    <div class="container mx-auto px-4">
      <!-- Footer widgets section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <!-- If we have widgets from WordPress, display them -->
        <div v-for="widget in footerWidgets" :key="widget.id" class="footer-widget">
          <h3 class="text-xl font-semibold mb-4">{{ widget.title }}</h3>
          <div v-html="widget.content"></div>
        </div>
        
        <!-- Fallback widgets if none from WordPress -->
        <template v-if="footerWidgets.length === 0">
          <!-- About widget -->
          <div class="footer-widget">
            <h3 class="text-xl font-semibold mb-4">About Us</h3>
            <p class="text-gray-300">
              This is a headless WordPress site built with Nuxt and WPGraphQL.
              Customize this footer in the WordPress admin or directly in this component.
            </p>
          </div>
          
          <!-- Quick Links widget -->
          <div class="footer-widget">
            <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
            <ul class="space-y-2">
              <li><NuxtLink to="/about" class="hover:text-blue-300">About</NuxtLink></li>
              <li><NuxtLink to="/contact" class="hover:text-blue-300">Contact</NuxtLink></li>
              <li><NuxtLink to="/privacy-policy" class="hover:text-blue-300">Privacy Policy</NuxtLink></li>
            </ul>
          </div>
          
          <!-- Contact widget -->
          <div class="footer-widget">
            <h3 class="text-xl font-semibold mb-4">Contact</h3>
            <address class="not-italic text-gray-300">
              <p>123 WordPress Lane</p>
              <p>Headless City, HL 12345</p>
              <p class="mt-2">Email: <a href="mailto:info@example.com" class="hover:text-blue-300">info@example.com</a></p>
            </address>
          </div>
        </template>
      </div>
      
      <!-- Footer navigation -->
      <div class="border-t border-gray-700 pt-6 pb-4">
        <nav class="flex flex-wrap justify-center mb-4">
          <div v-if="loading" class="text-gray-400">Loading menu...</div>
          <div v-else-if="error" class="text-red-400">Error loading footer menu</div>
          
          <!-- Display WordPress footer menu if available -->
          <template v-else>
            <NuxtLink 
              v-for="item in footerMenu" 
              :key="item.id" 
              :to="item.uri" 
              class="mx-3 hover:text-blue-300"
            >
              {{ item.label }}
            </NuxtLink>
            
            <!-- Fallback links if no WordPress menu is set -->
            <template v-if="footerMenu.length === 0">
              <NuxtLink to="/" class="mx-3 hover:text-blue-300">Home</NuxtLink>
              <NuxtLink to="/blog" class="mx-3 hover:text-blue-300">Blog</NuxtLink>
              <NuxtLink to="/privacy-policy" class="mx-3 hover:text-blue-300">Privacy</NuxtLink>
              <NuxtLink to="/sitemap" class="mx-3 hover:text-blue-300">Sitemap</NuxtLink>
            </template>
          </template>
        </nav>
      </div>
      
      <!-- Copyright -->
      <div class="text-center text-gray-400 text-sm">
        <p>© {{ currentYear }} {{ siteTitle }}. All rights reserved.</p>
        <p class="mt-1">Built with WordPress, Nuxt, and WPGraphQL.</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer-widget ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* Handle WordPress widget content styling */
:deep(.footer-widget a) {
  color: #d1d5db; /* text-gray-300 */
  text-decoration: none;
}

:deep(.footer-widget a:hover) {
  color: #93c5fd; /* text-blue-300 */
}
</style>