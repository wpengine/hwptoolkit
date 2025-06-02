<script setup>
import { computed, ref, watch } from 'vue';
import { useGraphQL, gql } from '../../lib/client';
import { getPostsPerPageSync } from '../../lib/utils';
import PostListing from './listing/Post.vue';

// Define pagination props
const props = defineProps({
  page: {
    type: Number,
    default: 1
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

// In an async function:
const fetchPosts = async () => {
  const postsPerPage = await getPostsPerPage();
  // Use postsPerPage in your query
};

// Or when you need it synchronously:
const postsPerPage = getPostsPerPageSync();
const isLoading = ref(false);

// Define GraphQL query similar to Next.js implementation
const POSTS_QUERY = gql`
  query GetPosts($first: Int = 9, $after: String, $category: String, $tag: String) {
    posts(
      first: $first, 
      after: $after, 
      where: {
        status: PUBLISH,
        categoryName: $category,
        tag: $tag
      }
    ) {
      pageInfo {
        hasNextPage
        endCursor
      }
      edges {
        cursor
        node {
          id
          title
          date
          excerpt
          uri
          slug
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
          categories {
            nodes {
              name
              slug
            }
          }
          author {
            node {
              name
              avatar {
                url
              }
            }
          }
        }
      }
    }
  }
`;

// Prepare variables for GraphQL query
const variables = computed(() => {
  return {
    first: postsPerPage,
    after: null,
    category: props.category || null,
    tag: props.tag || null
  };
});

// Fetch initial posts data
const { data, loading, error, refetch } = useGraphQL(
  POSTS_QUERY,
  variables.value
);

// Store all loaded posts
const allPosts = ref([]);
const pageInfo = ref(null);

// Update posts and pageInfo when data changes
const updatePostsData = () => {
  if (data.value?.posts) {
    const edges = data.value.posts.edges || [];

    // For initial load, replace all posts
    if (allPosts.value.length === 0) {
      allPosts.value = edges.map(edge => edge.node);
    }

    // Update pageInfo
    pageInfo.value = data.value.posts.pageInfo;
  }
};

// Watch for data changes to update posts
watch(() => data.value, updatePostsData, { immediate: true });

const loadMorePosts = async () => {
  // Early exit if no more posts or already loading
  if (!pageInfo.value?.hasNextPage || isLoading.value) {
    console.log('Cannot load more: ', {
      hasNextPage: pageInfo.value?.hasNextPage,
      isLoading: isLoading.value
    });
    return;
  }

  isLoading.value = true;
  console.log('Loading more posts with cursor:', pageInfo.value.endCursor);

  try {
    // Explicitly set all variables for clarity
    const loadMoreVariables = {
      first: postsPerPage,
      after: pageInfo.value.endCursor,
      category: props.category || null,
      tag: props.tag || null
    };

    console.log('Load more variables:', loadMoreVariables);

    // Use explicit refetch with all variables
    const result = await refetch(loadMoreVariables);

    console.log('Load more response:', result);

    // Verify the structure of the response
    if (!result.data?.posts?.edges) {
      console.error('Invalid response structure:', result);
      throw new Error('Invalid response structure from GraphQL');
    }

    // Check if we got any new posts
    const newPostsEdges = result.data.posts.edges || [];
    console.log(`Received ${newPostsEdges.length} new posts`);

    if (newPostsEdges.length > 0) {
      // Map to the node structure and add to existing posts
      const newPosts = newPostsEdges.map(edge => edge.node);
      allPosts.value = [...allPosts.value, ...newPosts];

      // Update pageInfo
      pageInfo.value = result.data.posts.pageInfo;
      console.log('Updated pageInfo:', pageInfo.value);
    } else {
      // No new posts were found
      console.log('No new posts found, marking hasNextPage as false');
      pageInfo.value = { ...pageInfo.value, hasNextPage: false };
    }
  } catch (err) {
    console.error('Error loading more posts:', err);
  } finally {
    isLoading.value = false;
  }
};

</script>

<template>
  <div>
    <header>
      <h1>{{ pageTitle }}</h1>
      <p v-if="!props.category && !props.tag">Latest posts and updates</p>
    </header>

    <!-- Error state -->
    <div v-if="error">
      <h2>Error</h2>
      <p>{{ error.message }}</p>
    </div>

    <!-- Initial loading state -->
    <div v-else-if="loading && allPosts.length === 0">
      <p>Loading posts...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="allPosts.length === 0 && !loading">
      <p>No posts found.</p>
    </div>

    <!-- Post listing -->
    <template v-else>
      <!-- Post listing component -->
      <PostListing :posts="allPosts" :loading="false" :cols="3" />

      <!-- Load more button - similar to BlogList.js -->
      <div v-if="pageInfo?.hasNextPage" class="load-more">
        <button @click="loadMorePosts" type="button" :disabled="isLoading || loading">
          {{ isLoading ? 'Loading...' : 'Load more' }}
        </button>
      </div>
    </template>
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