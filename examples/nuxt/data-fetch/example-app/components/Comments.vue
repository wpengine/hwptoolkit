<script setup>
import { computed, ref } from 'vue';
import { useGraphQL, gql } from '../lib/client';
import CommentItem from './templates/comments/CommentItem.vue';
import CommentForm from './templates/comments/CommentForm.vue';

const props = defineProps({
  postId: {
    type: String,
    required: true
  },
  contentType: {
    type: String,
    default: 'post',
    validator: (value) => ['post', 'page'].includes(value)
  }
});

const getCommentsQuery = (contentType) => {
  return gql`
    query GetComments($postId: ID!) {
      ${contentType}(id: $postId, idType: DATABASE_ID) {
        id
        commentCount
        comments(first: 50, where: {orderby: COMMENT_DATE}) {
          nodes {
            id
            content
            date
            author {
              node {
                name
                url
                avatar {
                  url
                }
              }
            }
            parentId
          }
        }
      }
    }
  `;
};

// Use the function to get the query
const { data, loading, error } = useGraphQL(
  getCommentsQuery(props.contentType), 
  { postId: props.postId }
);

// Computed properties for component
const content = computed(() => data.value?.[props.contentType] || null);
const comments = computed(() => content.value?.comments?.nodes || []);
const commentCount = computed(() => content.value?.commentCount || 0);

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

// Convert flat comments list to hierarchical threaded comments
const threadedComments = computed(() => {
  const commentMap = {};
  const rootComments = [];
  
  // First pass: create a map of all comments by ID
  comments.value.forEach(comment => {
    commentMap[comment.id] = {
      ...comment,
      replies: []
    };
  });
  
  // Second pass: build the tree structure
  comments.value.forEach(comment => {
    if (comment.parentId) {
      // This is a reply, add it to its parent's replies array
      if (commentMap[comment.parentId]) {
        commentMap[comment.parentId].replies.push(commentMap[comment.id]);
      }
    } else {
      // This is a root comment
      rootComments.push(commentMap[comment.id]);
    }
  });
  
  return rootComments;
});

// Form for adding new comments (optional enhancement)
const newComment = ref('');
const authorName = ref('');
const authorEmail = ref('');
const isSubmitting = ref(false);
const submitError = ref(null);

const handleCommentSuccess = (newComment) => {
  // In a real app, you'd want to update your comments list
  // This could be done by refetching the comments or by manually
  // adding the new comment to your existing list
  console.log('Comment added successfully:', newComment);
  
  // You might want to refetch comments to update the list
  // refetchComments();
  
  // Reset reply state
  replyToId.value = null;
};

// Handle comment submission error
const handleCommentError = (error) => {
  console.error('Error submitting comment:', error);
  // You might want to display an error notification
};
</script>

<template>
  <section class="comments-section mt-12 pt-8 border-t border-gray-200">
    <h2 class="text-2xl font-semibold mb-6">
      Comments ({{ commentCount }})
    </h2>
    
    <!-- Loading state -->
    <div v-if="loading" class="text-center py-6">
      <p>Loading comments...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="bg-red-50 p-4 rounded mb-6 text-center">
      <p class="text-red-600">{{ error.message }}</p>
    </div>
    
    <!-- No comments state -->
    <div v-else-if="comments.length === 0" class="bg-gray-50 p-6 rounded text-center">
      <p class="text-gray-600">No comments yet. Be the first to share your thoughts!</p>
    </div>
    
    <!-- Comments thread -->
    <div v-else class="space-y-6">
      <!-- Root comments with nested replies -->
      <div v-for="comment in threadedComments" :key="comment.id" class="comment-thread">
        <!-- Root comment -->
        <CommentItem 
          :comment="comment" 
        />
        
        <!-- Nested replies (if any) -->
        <div v-if="comment.replies.length > 0" class="replies pl-6 mt-3 space-y-3">
          <div v-for="reply in comment.replies" :key="reply.id" class="comment bg-white border border-gray-100 p-4 rounded">
            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div class="shrink-0">
                <img 
                  :src="reply.author.node.avatar?.url || 'https://www.gravatar.com/avatar/?d=mp'" 
                  alt="Avatar"
                  class="w-8 h-8 rounded-full"
                />
              </div>
              
              <!-- Reply content -->
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <h4 class="font-medium text-gray-900">
                    {{ reply.author.node.name || 'Anonymous' }}
                  </h4>
                  <time class="text-sm text-gray-500">{{ formatDate(reply.date) }}</time>
                </div>
                
                <div class="comment-content prose prose-sm max-w-none" v-html="reply.content"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Comment form (optional) -->
  <CommentForm 
    :post-id="Number(postId)"
    :is-reply="!!replyToId"
    @submit="handleCommentSubmit"
    @success="handleCommentSuccess"
    @error="handleCommentError"
    @cancel="cancelReply"
  />
  </section>
</template>

<style scoped>
:deep(.comment-content p) {
  margin-bottom: 0.5rem;
}

:deep(.comment-content p:last-child) {
  margin-bottom: 0;
}
</style>