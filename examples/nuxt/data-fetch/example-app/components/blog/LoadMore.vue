<script setup>
import { ref } from 'vue';
import { fetchGraphQL } from '../../lib/client';

const props = defineProps({
  initialPosts: {
    type: Array,
    required: true
  },
  initialPageInfo: {
    type: Object,
    required: true
  },
  postsPerPage: {
    type: Number,
    default: 10
  },
  postsQuery: {
    type: String,
    required: true
  },
  category: {
    type: String,
    default: ''
  },
  tag: {
    type: String,
    default: ''
  }
});

// Use an event emitter to communicate with parent
const emit = defineEmits(['update:posts', 'loading']);

const pageInfo = ref(props.initialPageInfo);
const loading = ref(false);
const error = ref(null);

const loadMorePosts = async () => {
  // Early exit if no more posts or already loading
  if (!pageInfo.value?.hasNextPage || loading.value) {
    return;
  }
  
  loading.value = true;
  emit('loading', true);
  
  try {
    // Execute GraphQL query
    const data = await fetchGraphQL(props.postsQuery, {
      first: props.postsPerPage,
      after: pageInfo.value.endCursor,
      category: props.category || null,
      tag: props.tag || null
    });
    
    const newPostsEdges = data?.posts?.edges || [];
    
    if (newPostsEdges.length > 0) {
      // Create new posts array
      const newPosts = newPostsEdges.map(edge => edge.node);
      
      // Emit the new posts to parent
      emit('update:posts', newPosts);
      
      // Update pageInfo
      pageInfo.value = data.posts.pageInfo;
    } else {
      // No new posts found
      pageInfo.value = { ...pageInfo.value, hasNextPage: false };
    }
  } catch (err) {
    console.error('Error loading more posts:', err);
    error.value = err;
  } finally {
    loading.value = false;
    emit('loading', false);
  }
};
</script>

<template>
  <!-- Only show load more button -->
  <div v-if="pageInfo?.hasNextPage" class="load-more">
    <button @click="loadMorePosts" type="button" :disabled="loading">
      {{ loading ? 'Loading...' : 'Load more' }}
    </button>
  </div>
</template>

<style>
.load-more {
  text-align: center;
  margin-top: 2rem;
}

.load-more button {
  padding: 0.75rem 2rem;
  background-color: #333;
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}

.load-more button:hover {
  background-color: #444;
}

.load-more button:disabled {
  background-color: #999;
  cursor: not-allowed;
}
</style>