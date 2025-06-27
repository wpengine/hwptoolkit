<script setup>
import CommentItem from "./CommentItem.vue";

defineProps({
  comment: {
    type: Object,
    required: true
  }
});

defineEmits(['reply']);
</script>

<template>
  <div class="comment-thread">
    <!-- The comment itself -->
    <CommentItem
      :comment="comment"
      @reply="$emit('reply', comment)"
    />

    <!-- Recursive rendering of nested replies -->
    <div v-if="comment.replies && comment.replies.length > 0" class="comment-replies">
      <CommentThread
        v-for="reply in comment.replies"
        :key="reply.id"
        :comment="reply"
        @reply="$emit('reply', $event)"
      />
    </div>
  </div>
</template>

<style scoped>
.comment-thread {
  margin-bottom: 1rem;
}

.comment-replies {
  margin-left: 2rem;
  border-left: 2px solid #e5e7eb;
  padding-left: 1rem;
  margin-top: 1rem;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .comment-replies {
    margin-left: 1rem;
    padding-left: 0.5rem;
  }
}
</style>