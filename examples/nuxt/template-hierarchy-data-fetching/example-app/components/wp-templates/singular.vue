<script setup>
import { computed } from "vue";
import { useGraphQL, gql } from "../../lib/client";
import { formatDate } from "../../lib/utils";
import Comments from "../Comments.vue";
import Loading from "../Loading.vue";
import NotFound from "../components/404.vue";

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
const uri = useRoute().path || "/";
const { data, loading, error } = useGraphQL(POST_QUERY, { slug: uri });

const post = computed(() => data.value?.post || null);
const postId = computed(() => post.value?.databaseId || null);
</script>

<template>
  <div class="container">
    <!-- Loading state -->
    <template v-if="loading">
      <Loading />
    </template>

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
      <h1 class="text-center">{{ post.title }}</h1>
      <!-- Featured image -->
      <img
        v-if="post.featuredImage?.node"
        :src="post.featuredImage.node.sourceUrl"
        :alt="post.featuredImage.node.altText || post.title"
        class=""
      />

      <!-- Post content -->
      <div class="prose prose-lg max-w-none mb-8" v-html="post.content"></div>

      <!-- Post meta -->
      <div class="post-meta">
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
        <div v-if="post.categories?.nodes?.length" class="flex">
          Categories:
          <NuxtLink
            v-for="category in post.categories.nodes"
            :key="category.name"
            :to="category.uri"
            class=""
          >
            {{ category.name }}
          </NuxtLink>
        </div>
        <!-- Tags -->
        <div v-if="post.tags?.nodes?.length" class="">
          <h3 class="">Tags:</h3>
          <div class="flex">
            <NuxtLink
              v-for="tag in post.tags.nodes"
              :key="tag.name"
              :to="tag.uri"
              class=""
            >
              {{ tag.name }}
            </NuxtLink>
          </div>
        </div>
      </div>
      <Comments v-if="postId" :post-id="postId" />
    </article>

    <template v-else>
      <NotFound />
    </template>
  </div>
</template>
