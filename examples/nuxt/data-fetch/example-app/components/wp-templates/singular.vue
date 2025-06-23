<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../lib/client';
import Comments from '../Comments.vue';

const POST_QUERY = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      id
      databaseId
      title
      date
      content
      commentCount
      categories {
        nodes {
          name
          uri
        }
      }
      tags {
        nodes {
          name
          uri
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
      featuredImage {
        node {
          sourceUrl
          altText
        }
      }
    }
  }
`;
const uri = useRoute().path || '/'
const { data, loading, error } = useGraphQL(POST_QUERY, { slug: uri });

const post = computed(() => data.value?.post || null);
const postId = computed(() => post.value?.databaseId || null);

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<template>
  <div class="container mx-auto p-4 max-w-3xl">
    <!-- Loading state -->
    <div v-if="loading" class="py-10 text-center">
      <p>Loading post...</p>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ error.message }}</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
    
    <!-- Post content -->
    <article v-else-if="post" class="py-10">
      <!-- Post header -->
      <header class="mb-8">
        <h1 class="text-4xl font-bold mb-4">{{ post.title }}</h1>
        
        <!-- Post meta -->
        <div class="flex items-center text-gray-500 mb-6">
          <div v-if="post.author?.node" class="flex items-center mr-6">
            <img
              v-if="post.author.node.avatar?.url"
              :src="post.author.node.avatar.url"
              :alt="post.author.node.name"
              class="w-8 h-8 rounded-full mr-2"
            />
            <span>By {{ post.author.node.name }}</span>
          </div>
          <time>{{ formatDate(post.date) }}</time>
        </div>
        
        <!-- Categories -->
        <div v-if="post.categories?.nodes?.length" class="flex flex-wrap gap-2 mb-6">
          <NuxtLink
            v-for="category in post.categories.nodes"
            :key="category.name"
            :to="category.uri"
            class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm hover:bg-blue-200"
          >
            {{ category.name }}
          </NuxtLink>
        </div>
      </header>
      
      <!-- Featured image -->
      <img 
        v-if="post.featuredImage?.node" 
        :src="post.featuredImage.node.sourceUrl" 
        :alt="post.featuredImage.node.altText || post.title"
        class="w-full h-auto rounded-lg mb-8 shadow-md"
      />
      
      <!-- Post content -->
      <div class="prose prose-lg max-w-none mb-8" v-html="post.content"></div>
      
      <!-- Tags -->
      <div v-if="post.tags?.nodes?.length" class="border-t pt-6 mt-8">
        <h3 class="text-lg font-semibold mb-3">Tags:</h3>
        <div class="flex flex-wrap gap-2">
          <NuxtLink
            v-for="tag in post.tags.nodes"
            :key="tag.name"
            :to="tag.uri"
            class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm hover:bg-gray-300"
          >
            {{ tag.name }}
          </NuxtLink>
        </div>
      </div>
      
      <!-- Comments section -->
      <Comments v-if="postId" :post-id="postId" />
    </article>
    
    <!-- Not found state -->
    <div v-else class="py-10 text-center">
      <h1 class="text-2xl font-bold mb-2">Post Not Found</h1>
      <p>The post you're looking for doesn't exist or has been removed.</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepage
      </NuxtLink>
    </div>
  </div>
</template>

<style scoped>

</style>