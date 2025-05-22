<script setup>
// No props or data fetching needed
</script>

<template>
  <div class="container mx-auto p-4 max-w-3xl py-20 text-center">
    <h1 class="text-4xl font-bold mb-4">Page Not Found</h1>
    <p class="text-xl text-gray-600 mb-8">
      The page you're looking for doesn't exist or may have been moved.
    </p>
    
    <div class="mt-8">
      <NuxtLink to="/" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
        Return to Homepage
      </NuxtLink>
    </div>
  </div>
</template>