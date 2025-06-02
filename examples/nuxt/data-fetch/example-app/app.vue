<script setup>
import { onMounted } from 'vue';
import { initializePostsPerPageCache } from './lib/utils';
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';

// Initialize on both client and server
initializePostsPerPageCache().catch(err => {
  console.error('Error initializing cache in app.vue:', err);
});

// Also try on mount just to be sure
onMounted(() => {
  initializePostsPerPageCache().catch(err => {
    console.error('Error initializing cache on mount:', err);
  });
});
</script>

<template>
  <div>
    <Header />
    <main class="container mx-auto px-4">
      <NuxtPage />
    </main>
    <Footer />
  </div>
</template>