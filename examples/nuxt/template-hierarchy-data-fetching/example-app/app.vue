<script setup>
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
</script>

<template>
  <div>
    <Header />
    <main id="main">
      <NuxtPage />
    </main>
    <Footer />
  </div>
</template>
