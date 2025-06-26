
<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  text: {
    type: String,
    default: '', // Default to empty string, so text only shows if provided
  },
});
</script>


<template>
  <div class="not-found text-center">
    <h1>404 Page Not Found</h1>
    <p>
      The page you're looking for doesn't exist or may have been moved.
    </p>
    <p v-if="text" class="loading-text">{{ text }}</p>
    <div class="mt-2">
      <NuxtLink to="/" class="button button-primary">
        Return to Homepage
      </NuxtLink>
    </div>
  </div>
</template>