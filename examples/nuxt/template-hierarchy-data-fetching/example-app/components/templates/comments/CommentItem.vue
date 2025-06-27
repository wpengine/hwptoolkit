<script setup>
import { computed } from 'vue';

const props = defineProps({
  comment: {
    type: Object,
    required: true
  }
});

defineEmits(['reply']);

// Format the comment date
const formattedDate = computed(() => {
  if (!props.comment.date) return '';
  return new Date(props.comment.date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Get avatar URL with fallback
const avatarUrl = computed(() => {
  return props.comment.author?.node?.avatar?.url || 
         `https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y`;
});

// Get author name with fallback
const authorName = computed(() => {
  return props.comment.author?.node?.name || 'Anonymous';
});

// Get author URL
const authorUrl = computed(() => {
  return props.comment.author?.node?.url;
});
</script>

<template>
  <article class="comment-item">
    <div class="comment-meta">
      <img 
        :src="avatarUrl" 
        :alt="`${authorName}'s avatar`"
        class="comment-avatar"
      />
      <div class="comment-info">
        <div class="comment-author">
          <a 
            v-if="authorUrl" 
            :href="authorUrl" 
            target="_blank" 
            rel="noopener noreferrer"
            class="author-link"
          >
            {{ authorName }}
          </a>
          <span v-else class="author-name">{{ authorName }}</span>
        </div>
        <time class="comment-date" :datetime="comment.date">
          {{ formattedDate }}
        </time>
      </div>
    </div>
    
    <div class="comment-content" v-html="comment.content"></div>
    
    <div class="comment-actions">
      <button 
        @click="$emit('reply', comment)"
        class="reply-button"
      >
        Reply
      </button>
    </div>
  </article>
</template>

<style scoped>
.comment-item {
  background: #f9fafb;
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.comment-meta {
  display: flex;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.comment-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.comment-info {
  flex: 1;
}

.comment-author {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.author-link {
  color: #3b82f6;
  text-decoration: none;
}

.author-link:hover {
  text-decoration: underline;
}

.author-name {
  color: #374151;
}

.comment-date {
  color: #6b7280;
  font-size: 0.875rem;
}

.comment-content {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.comment-content :deep(p) {
  margin-bottom: 0.5rem;
}

.comment-content :deep(p:last-child) {
  margin-bottom: 0;
}

.comment-actions {
  display: flex;
  gap: 1rem;
}

.reply-button {
  background: none;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  font-size: 0.875rem;
  padding: 0.25rem 0;
}

.reply-button:hover {
  text-decoration: underline;
}
</style>