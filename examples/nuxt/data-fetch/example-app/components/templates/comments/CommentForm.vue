<script setup>
import { ref } from 'vue';
import { useMutation, gql } from '../../../lib/client'; // Update path as needed

const props = defineProps({
  postId: {
    type: Number,
    required: true
  },
  parentId: {
    type: Number,
    default: 0
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
const errorMessage = ref('');

// GraphQL mutation for creating a comment
const CREATE_COMMENT = gql`
  mutation CreateComment(
    $commentOn: Int!, 
    $content: String!, 
    $author: String!, 
    $parent: ID
  ) {
    createComment(
      input: {
        commentOn: $commentOn, 
        content: $content, 
        author: $author, 
        parent: $parent
      }
    ) {
      success
      comment {
        id
        content
        date
        author {
          node {
            name
          }
        }
      }
    }
  }
`;

// Form submission
const submitComment = async () => {
  if (!authorName.value || !authorEmail.value || !commentContent.value) {
    return;
  }
  
  isSubmitting.value = true;
  errorMessage.value = '';
  
  try {
    // Create variables for the mutation
    const variables = {
      commentOn: props.postId,
      content: commentContent.value,
      author: authorName.value,
      authorEmail: authorEmail.value
    };
    
    // Add parent ID for replies
    if (props.parentId > 0) {
      variables.parent = String(props.parentId);
    }
    
    // Execute the mutation
    const { data, errors } = await useMutation(CREATE_COMMENT, variables);
    
    if (errors) {
        console.error('GraphQL errors:', errors);
      throw new Error(errors[0]?.message || 'Failed to submit comment');
    }
    
    if (!data?.createComment?.success) {
      throw new Error('Comment submission failed');
    }
    
    // Emit success event with the new comment
    emit('success', data.createComment.comment);
    
    // Clear form after submission
    authorName.value = '';
    authorEmail.value = '';
    commentContent.value = '';
    
    // Emit submit event for parent component
    emit('submit', {
      name: authorName.value,
      content: commentContent.value,
      postId: props.postId,
      parentId: props.parentId
    });
  } catch (error) {
    console.error('Error submitting comment:', error);
    errorMessage.value = error.message || 'Failed to submit comment';
    emit('error', error);
  } finally {
    isSubmitting.value = false;
  }
};

// Cancel reply
const cancelReply = () => {
  emit('cancel');
};
</script>

<template>
  <div>
    <h3>{{ isReply ? 'Reply to Comment' : 'Leave a Comment' }}</h3>
    
    <form @submit.prevent="submitComment">
      <div>
        <label for="author-name">Name:</label>
        <input 
          id="author-name"
          v-model="authorName"
          type="text"
          required
        />
      </div>
      
      <div>
        <label for="author-email">Email:</label>
        <input 
          id="author-email"
          v-model="authorEmail"
          type="email"
          required
        />
      </div>
      
      <div>
        <label for="comment-content">Comment:</label>
        <textarea 
          id="comment-content"
          v-model="commentContent"
          rows="4"
          required
        ></textarea>
      </div>
      
      <!-- Error message -->
      <div v-if="errorMessage" style="color: red; margin-top: 10px;">
        {{ errorMessage }}
      </div>
      
      <div>
        <button type="submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Posting...' : 'Post Comment' }}
        </button>
        
        <button 
          v-if="isReply" 
          type="button" 
          @click="cancelReply"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>