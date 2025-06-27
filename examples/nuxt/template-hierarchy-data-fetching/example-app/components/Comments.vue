<script setup>
import { computed, ref } from "vue";
import { useGraphQL, gql, fetchGraphQL } from "../lib/client";
import CommentForm from "./templates/comments/CommentForm.vue";
import CommentThread from "./templates/comments/CommentThread.vue";

const props = defineProps({
  postId: {
    type: Number,
    required: true,
  },
  contentType: {
    type: String,
    default: "post",
    validator: (value) => ["post", "page"].includes(value),
  },
  commentsPerPage: {
    type: Number,
    default: 10,
  },
});

const getCommentsQuery = (contentType) => {
  return gql`
    query GetComments($postId: ID!, $first: Int, $after: String) {
      ${contentType}(id: $postId, idType: DATABASE_ID) {
        id
        commentCount
        comments(first: $first, after: $after, where: {orderby: COMMENT_DATE}) {
          pageInfo {
            hasNextPage
            endCursor
          }
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
const { data, loading, error, refetch } = useGraphQL(
  getCommentsQuery(props.contentType),
  {
    postId: props.postId,
    first: props.commentsPerPage,
  },
  { key: `comments-${props.contentType}-${props.postId}` }
);

// Additional state for pagination
const allComments = ref([]);
const pageInfo = ref({ hasNextPage: false, endCursor: null });
const loadingMore = ref(false);

// Watch for initial data load
const content = computed(() => data.value?.[props.contentType] || null);
const comments = computed(() => {
  if (content.value?.comments?.nodes) {
    // Update allComments if this is the initial load
    if (allComments.value.length === 0) {
      allComments.value = [...content.value.comments.nodes];
      pageInfo.value = content.value.comments.pageInfo;
    }
    return allComments.value;
  }
  return [];
});

const commentCount = computed(() => content.value?.commentCount || 0);

const replyData = ref(null);
const showCommentForm = ref(true);

// Recursive function to build nested comment structure
const buildCommentTree = (comments, parentId = null) => {
  return comments
    .filter(comment => comment.parentId === parentId)
    .map(comment => ({
      ...comment,
      replies: buildCommentTree(comments, comment.id)
    }));
};

// Convert flat comments list to hierarchical threaded comments
const threadedComments = computed(() => {
  return buildCommentTree(comments.value);
});

const loadMoreComments = async () => {
  if (!pageInfo.value.hasNextPage || loadingMore.value) {
    return;
  }

  loadingMore.value = true;

  try {
    const moreCommentsData = await fetchGraphQL(
      getCommentsQuery(props.contentType),
      {
        postId: props.postId,
        first: props.commentsPerPage,
        after: pageInfo.value.endCursor,
      }
    );

    if (moreCommentsData?.[props.contentType]?.comments?.nodes) {
      const newComments = moreCommentsData[props.contentType].comments.nodes;

      // Add new comments to existing ones
      allComments.value = [...allComments.value, ...newComments];

      // Update pagination info
      pageInfo.value = moreCommentsData[props.contentType].comments.pageInfo;
    }
  } catch (error) {
    console.error("Error loading more comments:", error);
  } finally {
    loadingMore.value = false;
  }
};

// Handle reply to a specific comment
const handleReply = (comment) => {
  replyData.value = {
    author: comment.author.node.name,
    parentId: comment.id, // Set parentId to the comment being replied to
  };
  showCommentForm.value = true;

  setTimeout(() => {
    const formElement = document.getElementById("comment-form");
    if (formElement) {
      formElement.scrollIntoView({ behavior: "smooth" });
    }
  }, 100);
};

const cancelReply = () => {
  replyData.value = null;
  showCommentForm.value = true;
};

const handleCommentSubmit = (commentData) => {
  //console.log("Comment submitted:", commentData);
};

const handleCommentSuccess = (newComment) => {
  //console.log("Comment added successfully:", newComment);

  allComments.value = [];
  pageInfo.value = { hasNextPage: false, endCursor: null };

  if (refetch) {
    refetch();
  }

  replyData.value = null;
  showCommentForm.value = true;
};

const handleCommentError = (error) => {
  console.error("Error submitting comment:", error);
};
</script>

<template>
  <section id="comments">
    <h2>Comments ({{ commentCount }})</h2>

    <!-- Loading state -->
    <div v-if="loading">
      <p>Loading comments...</p>
    </div>

    <!-- Error state -->
    <div v-else-if="error">
      <p>{{ error.message }}</p>
    </div>

    <!-- No comments state -->
    <div v-else-if="comments.length === 0">
      <p>No comments yet. Be the first to share your thoughts!</p>
    </div>

    <!-- Comments thread -->
    <div v-else>
      <!-- Recursive comment rendering -->
      <CommentThread
        v-for="comment in threadedComments"
        :key="comment.id"
        :comment="comment"
        @reply="handleReply"
      />

      <!-- Load More Comments Button -->
      <div v-if="pageInfo.hasNextPage" class="load-more-comments">
        <button
          @click="loadMoreComments"
          :disabled="loadingMore"
          class="load-more-button"
        >
          {{ loadingMore ? "Loading..." : "Load More Comments" }}
        </button>
      </div>

      <!-- Comments summary -->
      <div
        v-if="!pageInfo.hasNextPage && comments.length > 0"
        class="comments-summary"
      >
        <p>Showing all {{ comments.length }} comments</p>
      </div>
    </div>

    <!-- Comment form -->
    <CommentForm
      v-if="showCommentForm"
      :post-id="Number(postId)"
      :replyData="replyData || null"
      @submit="handleCommentSubmit"
      @success="handleCommentSuccess"
      @error="handleCommentError"
      @cancel="cancelReply"
    />
  </section>
</template>

<style scoped>
#comments {
  margin-top: 2rem;
  border-top: 1px solid #e5e7eb;
  padding-top: 2rem;
}

.load-more-comments {
  text-align: center;
  margin: 2rem 0;
}

.load-more-button {
  background-color: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.2s;
}

.load-more-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.load-more-button:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
}

.comments-summary {
  text-align: center;
  margin: 2rem 0;
  color: #6b7280;
  font-size: 0.9rem;
}
</style>