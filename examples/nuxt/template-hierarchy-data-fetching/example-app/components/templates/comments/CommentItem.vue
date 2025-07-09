<script setup>
import { computed } from "vue";
import { formatDate } from "~/lib/utils";
const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
});

defineEmits(["reply"]);

const avatarUrl = computed(() => {
  return (
    props.comment.author?.node?.avatar?.url ||
    `https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y`
  );
});

const authorName = computed(() => {
  return props.comment.author?.node?.name || "Anonymous";
});

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
          {{ formatDate(comment.date) }}
        </time>
      </div>
    </div>

    <div class="comment-content" v-html="comment.content"></div>

    <div class="comment-actions">
      <button
        @click="$emit('reply', comment)"
        class="button button-secondary button-small reply-button"
      >
        Reply
      </button>
    </div>
  </article>
</template>

<style scoped>
@import "/assets/scss/components/comments/comment-item.scss";
</style>
