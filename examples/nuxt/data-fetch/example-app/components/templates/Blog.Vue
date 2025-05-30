<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../lib/client';

const BLOG_QUERY = gql`
  query GetBlogPosts($first: Int = 10, $after: String) {
    posts(first: $first, after: $after, where: {status: PUBLISH}) {
      pageInfo {
        hasNextPage
        endCursor
      }
      nodes {
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
`;

// Pagination
const currentPage = ref(1);
const postsPerPage = 10;
const cursor = ref(null);

// Fetch blog posts
const { data, loading, error, fetchMore } = useGraphQL(
  BLOG_QUERY, 
  { first: postsPerPage }
);

// Computed properties
const posts = computed(() => data.value?.posts?.nodes || []);
const pageInfo = computed(() => data.value?.posts?.pageInfo || {});

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Load more posts
const loadMorePosts = async () => {
  if (!pageInfo.value.hasNextPage) return;
  
  await fetchMore({
    variables: {
      after: pageInfo.value.endCursor
    },
    updateQuery: (prev, { fetchMoreResult }) => {
      if (!fetchMoreResult) return prev;
      
      return {
        posts: {
          ...fetchMoreResult.posts,
          nodes: [
            ...prev.posts.nodes,
            ...fetchMoreResult.posts.nodes
          ]
        }
      };
    }
  });
  
  currentPage.value++;
};

// Format WordPress URL
const formatWordPressUrl = (uri) => {
  if (!uri) return '/';
  
  // Remove the leading slash if present
  let cleanUri = uri.startsWith('/') ? uri.substring(1) : uri;
  
  // Remove trailing slash if present (except for root)
  cleanUri = cleanUri.endsWith('/') && cleanUri !== '/'
    ? cleanUri.slice(0, -1)
    : cleanUri;
    
  return `/${cleanUri}`;
};
</script>

<template>
  <div class="container mx-auto p-4 max-w-4xl">
    <header class="text-center mb-12">
      <h1 class="text-4xl font-bold mb-4">Blog</h1>
      <p class="text-xl text-gray-600">Latest posts and updates</p>
    </header>
    
    <!-- Loading state -->
    <div v-if="loading && posts.length === 0" class="py-10 text-center">
      <p>Loading blog posts...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="py-10 text-center">
      <h2 class="text-2xl font-bold text-red-500 mb-2">Error</h2>
      <p>{{ error.message }}</p>
    </div>
    
    <!-- Posts listing -->
    <div v-else-if="posts.length > 0" class="space-y-12">
      <!-- Individual post -->
      <article v-for="post in posts" :key="post.id" class="border-b pb-8 mb-8 last:border-0">
        <!-- Post header -->
        <header class="mb-4">
          <h2 class="text-2xl font-bold mb-2">
            <NuxtLink :to="formatWordPressUrl(post.uri)" class="hover:text-blue-600 transition-colors">
              {{ post.title }}
            </NuxtLink>
          </h2>
          
          <!-- Post meta -->
          <div class="flex items-center text-gray-500 mb-3">
            <div v-if="post.author?.node" class="flex items-center mr-4">
              <img
                v-if="post.author.node.avatar?.url"
                :src="post.author.node.avatar.url"
                :alt="post.author.node.name"
                class="w-6 h-6 rounded-full mr-2"
              />
              <span>{{ post.author.node.name }}</span>
            </div>
            <time>{{ formatDate(post.date) }}</time>
            
            <!-- Categories -->
            <div v-if="post.categories?.nodes?.length" class="ml-4 flex gap-2">
              <span>in</span>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="(category, index) in post.categories.nodes"
                  :key="category.slug"
                >
                  {{ category.name }}{{ index < post.categories.nodes.length - 1 ? ', ' : '' }}
                </span>
              </div>
            </div>
          </div>
        </header>
        
        <!-- Featured image -->
        <div v-if="post.featuredImage?.node" class="mb-4">
          <NuxtLink :to="formatWordPressUrl(post.uri)">
            <img 
              :src="post.featuredImage.node.sourceUrl" 
              :alt="post.featuredImage.node.altText || post.title"
              class="w-full h-auto rounded-lg shadow-sm"
            />
          </NuxtLink>
        </div> 
        
        <!-- Excerpt -->
        <div class="prose prose-lg max-w-none mb-4" v-html="post.excerpt"></div>
        
        <!-- Read more link -->
        <div class="mt-4">
          <NuxtLink 
            :to="formatWordPressUrl(post.uri)" 
            class="text-blue-600 hover:underline font-medium"
          >
            Read more →
          </NuxtLink>
        </div>
      </article>
      
      <!-- Load more button -->
      <div class="text-center py-8">
        <button 
          v-if="pageInfo.hasNextPage"
          @click="loadMorePosts" 
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          :disabled="loading"
        >
          <span v-if="loading">Loading...</span>
          <span v-else>Load More Posts</span>
        </button>
        <p v-else class="text-gray-500">No more posts to load</p>
      </div>
    </div>
    
    <!-- Empty state -->
    <div v-else class="py-10 text-center">
      <p class="text-gray-500">No blog posts found</p>
    </div>
  </div>
</template>