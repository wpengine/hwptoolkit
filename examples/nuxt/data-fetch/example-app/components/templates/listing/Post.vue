<script setup>
import { computed } from 'vue';

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
});

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString();
};

// Format WordPress URL
const formatWordPressUrl = (uri) => {
  if (!uri) return '/';
  
  // Remove the leading slash if present
  let cleanUri = uri.startsWith('/') ? uri.substring(1) : uri;
  
  // Remove trailing slash if present (except for root)
  cleanUri = cleanUri.endsWith('/') && cleanUri !== '/' 
    ? cleanUri.slice(0, -1) 
    : cleanUri;
    
  return `/${cleanUri}`;
};

// Get post link
const postLink = computed(() => {
  if (props.post.uri) return formatWordPressUrl(props.post.uri);
  if (props.post.slug) return `/posts/${props.post.slug}`;
  return '/';
});

// Create a safe excerpt without HTML tags
const safeExcerpt = computed(() => {
  if (!props.post.excerpt) return '';
  // Strip HTML tags and limit length
  return props.post.excerpt
    .replace(/<\/?[^>]+(>|$)/g, "") // Remove HTML tags
    .substring(0, 120) // Limit to 120 chars
    .trim() + (props.post.excerpt.length > 120 ? '...' : '');
});

// Get featured image if available
const featuredImage = computed(() => 
  props.post.featuredImage?.node?.sourceUrl || null
);
</script>

<template>
  <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    <!-- Featured Image (if available) -->
    <div v-if="featuredImage" class="h-48 overflow-hidden">
      <img :src="featuredImage" :alt="post.title" class="w-full h-full object-cover" />
    </div>
    
    <div class="p-6">
      <!-- Post Title -->
      <h3 class="text-xl font-semibold mb-2">
        <NuxtLink :to="postLink" class="hover:text-blue-600 transition-colors">
          {{ post.title }}
        </NuxtLink>
      </h3>
      
      <!-- Post Date -->
      <time class="text-sm text-gray-500 block mb-3">{{ formatDate(post.date) }}</time>
      
      <!-- Post Excerpt -->
      <div v-if="post.excerpt" class="text-gray-600 mb-4">
        {{ safeExcerpt }}
      </div>
      
      <!-- Read More Link -->
      <NuxtLink :to="postLink" class="inline-block text-blue-600 font-medium hover:underline">
        Read More →
      </NuxtLink>
    </div>
  </article>
</template>