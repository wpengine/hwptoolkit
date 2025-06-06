<script setup>
import { computed } from 'vue';

const props = defineProps({
  comment: {
    type: Object,
    required: true
  }
});

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Compute author name with fallback
const authorName = computed(() => {
  return props.comment.author?.node?.name || 'Anonymous';
});

// Compute avatar URL with fallback
const avatarUrl = computed(() => {
  return props.comment.author?.node?.avatar?.url || 'https://www.gravatar.com/avatar/?d=mp';
});

// Compute CSS classes based on whether this is a reply
const containerClasses = computed(() => {
  return props.isReply
    ? 'comment bg-white border border-gray-100 p-4 rounded'
    : 'comment bg-gray-50 p-4 rounded';
});

const avatarClasses = computed(() => {
  return props.isReply
    ? 'w-8 h-8 rounded-full'
    : 'w-10 h-10 rounded-full';
});
</script>

<template>
  <div :class="containerClasses">
    <div class="flex items-start gap-4">
      <!-- Avatar -->
      <div class="shrink-0">
        <img 
          :src="avatarUrl" 
          :alt="authorName"
          :class="avatarClasses"
        />
      </div>
      
      <!-- Comment content -->
      <div class="flex-1">
        <div class="flex items-center justify-between mb-1">
          <h4 class="font-medium text-gray-900">{{ authorName }}</h4>
          <time class="text-sm text-gray-500">{{ formatDate(comment.date) }}</time>
        </div>
        
        <div class="comment-content prose prose-sm max-w-none" v-html="comment.content"></div>
        
        <!-- Reply button could go here -->
        <div class="mt-2 text-right">
          <button 
            class="text-sm text-blue-600 hover:underline"
            @click="$emit('reply', comment.id)"
          >
            Reply
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.comment-content p) {
  margin-bottom: 0.5rem;
}

:deep(.comment-content p:last-child) {
  margin-bottom: 0;
}
</style>