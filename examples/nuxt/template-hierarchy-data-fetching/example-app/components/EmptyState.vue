<script setup>
import { defineProps } from "vue";

const props = defineProps({
  text: {
    type: String,
    default: "", // Default to empty string, so text only shows if provided
  },
});
</script>

<template>
  <div class="empty-state-container text-center">
    <p v-if="text" class="loading-text">{{ text }}</p>
    <p v-else class="loading-text">Nothing to see here yet.</p>
  </div>
</template>
