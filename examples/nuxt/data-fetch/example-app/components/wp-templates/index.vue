<script setup>
const props = defineProps({
  uri: String,
  templateData: Object,
  seedQuery: Object
});

const node = computed(() => props.seedQuery?.nodeByUri);
</script>

<template>
  <div class="container mx-auto p-4 max-w-3xl py-10">
    <div v-if="node">
      <h1 class="text-4xl font-bold mb-6">{{ node.title || 'Untitled' }}</h1>
      <div v-if="node.content" class="prose max-w-none" v-html="node.content"></div>
      <div v-else class="text-gray-500">No content available.</div>
    </div>
    <div v-else class="text-center">
      <h1 class="text-2xl font-bold mb-2">Page Not Found</h1>
      <NuxtLink to="/" class="text-blue-500 hover:underline">Return home</NuxtLink>
    </div>
  </div>
</template>