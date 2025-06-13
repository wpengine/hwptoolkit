
<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  text: {
    type: String,
    default: '', // Default to empty string, so text only shows if provided
  },
  showSpinner: {
    type: Boolean,
    default: true, // Show spinner by default
  },
});
</script>

<template>
  <div class="loading-container">
    <div v-if="showSpinner" class="spinner"></div>
    <p v-if="text" class="loading-text">{{ text }}</p>
  </div>
</template>

<style scoped lang="scss">
@use '@/assets/scss/components/loading';
</style>