<script setup>
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
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