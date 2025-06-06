<script setup>
import { formatWordPressUrl, formatDate, createExcerpt } from '../../../lib/utils';

const props = defineProps({
  posts: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  cols: {
    type: Number,
    default: 3
  }
});


</script>

<template>

  <!-- Loading state -->
  <div v-if="loading && posts.length === 0">
    <p>Loading posts...</p>
  </div>

  <!-- Posts listing -->
  <div class="grid blog-listing" :class="`cols-${cols}`" v-else-if="posts.length > 0">
    <!-- Individual post -->
    <article v-for="post in posts" :key="post.id" class="post-card">

      <!-- Featured image -->
      <div v-if="post.featuredImage?.node" class="featured-image-container">
        <NuxtLink :to="formatWordPressUrl(post.uri)">
          <img :src="post.featuredImage.node.sourceUrl" :alt="post.featuredImage.node.altText || post.title"
            class="feature-img" />
        </NuxtLink>
      </div>
      <div class="post-card-content">
      <header>
        <h3 class="post-title">
          <NuxtLink :to="post.uri">
            {{ post.title }}
          </NuxtLink>
        </h3>

        <div>
          <div class="post-meta">
            <div v-if="post.author?.node" class="author">
              <img v-if="post.author.node.avatar?.url" :src="post.author.node.avatar.url" :alt="post.author.node.name"
                width="24" height="24" class="author-avatar" />
              <span class="author-name">{{ post.author.node.name }}</span>
            </div>
            <time>{{ formatDate(post.date) }}</time>
          </div>
          <!-- Categories -->
          <div v-if="post.categories?.nodes?.length" class="categories-container">
            <span>Cats:</span>
            <div class="post-categories">
              <span v-for="(category, index) in post.categories.nodes" :key="category.slug">
                {{ category.name }}{{ index < post.categories.nodes.length - 1 ? ', ' : '' }} </span>
            </div>
          </div>
        </div>
      </header>


      <!-- Excerpt -->
      <div v-html="createExcerpt(post.excerpt, 150)" class="post-excerpt"></div>

      <!-- Read more link -->
      <div class="read-more">
        <NuxtLink :to="formatWordPressUrl(post.uri)" class="button button-primary ">
          Read more →
        </NuxtLink>
      </div>

      </div>
    </article>
  </div>
  <div v-else>
    <p>No posts found</p>
  </div>
</template>

<style lang="scss">
@use '@/assets/scss/components/post-card';
</style>