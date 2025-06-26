<script setup>
import { computed } from "vue";
import { formatDate } from "../../../lib/utils"; // Assuming you have a utility for date formatting

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
  isReply: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["reply"]);

// Compute author name with fallback
const authorName = computed(() => {
  return props.comment.author?.node?.name || "Anonymous";
});

// Compute avatar URL with fallback
const avatarUrl = computed(() => {
  return (
    props.comment.author?.node?.avatar?.url ||
    "https://www.gravatar.com/avatar/?d=mp"
  );
});
</script>

<template>
  <div class="comment-item" :class="{ 'is-reply': isReply }">
    <div class="comment-content">
      <div class="comment-header">
        <div class="author-info">
          <div class="avatar">
            <img :src="avatarUrl" :alt="authorName" />
          </div>
          <h4>{{ authorName }}</h4>
        </div>
        <time class="comment-date">{{ formatDate(comment.date) }}</time>
      </div>
      <div v-html="comment.content"></div>
      <div class="comment-footer">
        <button
          @click="$emit('reply', comment.id)"
          class="button button-secondary button-small reply-button"
        >
          Reply
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use "@/assets/scss/components/comments/comment-item";
</style>
