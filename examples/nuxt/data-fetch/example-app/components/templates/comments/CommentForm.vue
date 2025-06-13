<script setup>
import { ref } from 'vue';
import { useMutation, gql } from '../../../lib/client';

const props = defineProps({
  postId: {
    type: Number,
    required: true
  },
  parent: {
    type: String,
    default: ''  // 0 means it's a root comment, not a reply
  },
  isReply: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel', 'success', 'error']);

// Form fields
const authorName = ref('');
const authorEmail = ref('');
const commentContent = ref('');
const isSubmitting = ref(false);

// Message states
const errorMessage = ref('');
const successMessage = ref('');
const showMessage = ref(false);


const CREATE_COMMENT = gql`
  mutation CreateComment(
    $commentOn: Int!, 
    $content: String!, 
    $author: String!, 
    $authorEmail: String!,
    $parent: ID,
  ) {
    createComment(
      input: {
        commentOn: $commentOn, 
        content: $content, 
        author: $author,
        authorEmail: $authorEmail,
        parent: $parent
      }
    ) {
      success
      comment {
        id
        content
        date
        parentId
        author {
          node {
            name
          }
        }
      }
    }
  }
`;

// Clear messages
const clearMessages = () => {
  errorMessage.value = '';
  successMessage.value = '';
  showMessage.value = false;
};

// Show success message
const showSuccess = (message) => {
  clearMessages();
  successMessage.value = message;
  showMessage.value = true;
  
  // Auto-hide success message after 5 seconds
  setTimeout(() => {
    clearMessages();
  }, 5000);
};

// Show error message
const showError = (message) => {
  clearMessages();
  errorMessage.value = message;
  showMessage.value = true;
  
  setTimeout(() => {
    clearMessages();
  }, 8000);
};

const dismissMessage = () => {
  clearMessages();
};


const submitComment = async () => {
  if (!authorName.value || !authorEmail.value || !commentContent.value) {
    showError('Please fill in all required fields.');
    return;
  }
  
  isSubmitting.value = true;
  clearMessages();
  
  try { 
    const variables = {
      commentOn: props.postId,
      content: commentContent.value,
      author: authorName.value,
      authorEmail: authorEmail.value,
      parent: null
    };
    
    // Add parent ID for replies (only if it's actually a reply)
    if (props.parent) {
      // Convert base64 parentId to the needed format and extract the number
      let base64ParentId = atob(`${props.parent}`);
      // Extract number from string like "comment:43"
      let parentNumber = parseInt(base64ParentId.split(':')[1], 10);
      variables.parent = parentNumber;    
    }

    console.log('Mutation variables:', variables);
    
    // Execute the mutation
    const { data, errors } = await useMutation(CREATE_COMMENT, variables);
    
    if (errors && errors.length > 0) {
      console.error('GraphQL errors:', errors);
      throw new Error(errors[0]?.message || 'Failed to submit comment');
    }
    
    if (!data?.createComment?.success) {
      throw new Error('Comment submission failed');
    }
    
    console.log('Comment created:', data.createComment.comment);
    
    // Show success message
    showSuccess(props.isReply ? 'Your reply has been posted successfully!' : 'Your comment has been posted successfully!');
    
    // Emit success event with the new comment
    emit('success', data.createComment.comment);
    
    // Clear form after successful submission
    authorName.value = '';
    authorEmail.value = '';
    commentContent.value = '';
    
    // Emit submit event for parent component
    emit('submit', {
      name: authorName.value,
      content: commentContent.value,
      postId: props.postId,
      parent: props.parent
    });
  } catch (error) {
    console.error('Error submitting comment:', error);
    showError(error.message || 'Failed to submit comment. Please try again.');
    emit('error', error);
  } finally {
    isSubmitting.value = false;
  }
};

// Cancel reply
const cancelReply = () => {
  // Clear form when canceling
  authorName.value = '';
  authorEmail.value = '';
  commentContent.value = '';
  clearMessages();
  
  emit('cancel');
};
</script>


<template>
  <div id="comment-form" class="comment-form">
    <h3 class="text-center">{{ isReply ? 'Reply to Comment' : 'Leave a Comment' }}</h3>
    <!-- Success/Error Message -->
    <div v-if="showMessage" class="message-container">
      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">
        <div class="message-content">
          <span class="message-icon">✓</span>
          <span class="message-text">{{ successMessage }}</span>
        </div>
        <button @click="dismissMessage" class="dismiss-button" type="button">
          ×
        </button>
      </div>
      
      <!-- Error Message -->
      <div v-if="errorMessage" class="error-message">
        <div class="message-content">
          <span class="message-icon">⚠</span>
          <span class="message-text">{{ errorMessage }}</span>
        </div>
        <button @click="dismissMessage" class="dismiss-button" type="button">
          ×
        </button>
      </div>
    </div>
    
    <form @submit.prevent="submitComment" class="grid gap-4">
  
        <div class="grid cols-2 gap-4">
        
        <div class="flex-1">
          <label for="author-name" class="sr-only">
            Name *
          </label>
          <input 
            id="author-name"
            v-model="authorName"
            type="text"
            required
            placeholder="Name*"
          />
        </div>
        
        <div>
          <label for="author-email" class="sr-only">
            Email *
          </label>
          <input 
            id="author-email"
            v-model="authorEmail"
            type="email"
            required
            placeholder="Email* (will not be published)"
          />
        </div>
      </div>
  
      
      <div class="row">
        <label for="comment-content" class="sr-only">
          Comment *
        </label>
        <textarea 
          id="comment-content"
          v-model="commentContent"
          rows="4"
          required
          placeholder="Your comment*"
        ></textarea>
      </div>
      
      <div class="gap-4 flex justify-end">
        <button 
          type="submit" 
          class="button button-primary"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Posting...' : 'Post Comment' }}
        </button>
        
        <button 
          v-if="isReply" 
          type="button" 
          @click="cancelReply"
          class="button button-secondary"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped lang="scss">
@use '@/assets/scss/components/comments/comment-form';
</style>