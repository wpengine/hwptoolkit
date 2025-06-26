<script setup>
import { formatDate, createExcerpt, getCategoryLink } from "../../../lib/utils";

const props = defineProps({
  posts: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  cols: {
    type: Number,
    default: 3,
  },
});
</script>

<template>
  <!-- Loading state -->
  <div v-if="loading && posts.length === 0">
    <p>Loading posts...</p>
  </div>

  <!-- Posts listing -->
  <div
    class="grid blog-listing"
    :class="`cols-${cols}`"
    v-else-if="posts.length > 0"
  >
    <!-- Individual post -->
    <article v-for="post in posts" :key="post.id" class="post-card">
      <!-- Featured image -->
      <div v-if="post.featuredImage?.node" class="featured-image-container">
        <NuxtLink :to="post.uri">
          <img
            :src="post.featuredImage.node.sourceUrl"
            :alt="post.featuredImage.node.altText || post.title"
            class="feature-img"
          />
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
                <img
                  v-if="post.author.node.avatar?.url"
                  :src="post.author.node.avatar.url"
                  :alt="post.author.node.name"
                  width="24"
                  height="24"
                  class="author-avatar"
                />
                <span class="author-name">{{ post.author.node.name }}</span>
              </div>
              <time>{{ formatDate(post.date) }}</time>
            </div>
            <!-- Categories -->
            <div
              v-if="post.categories?.nodes?.length"
              class="categories-container"
            >
              <span>Categories:</span>
              <ul class="post-categories">
                <li
                  v-for="(category, index) in post.categories.nodes"
                  :key="category.slug"
                >
                  <NuxtLink :to="getCategoryLink(category.slug)">
                    {{ category.name }} </NuxtLink
                  >{{ index < post.categories.nodes.length - 1 ? ", " : "" }}
                </li>
              </ul>
            </div>
            <div v-if="post.tags?.nodes?.length" class="tags-container">
              <span>Tags:</span>
              <ul class="post-tags">
                <li v-for="(tag, index) in post.tags.nodes" :key="tag.slug">
                  <NuxtLink :to="getCategoryLink(tag.slug)">
                    {{ tag.name }} </NuxtLink
                  >{{ index < post.tags.nodes.length - 1 ? ", " : "" }}
                </li>
              </ul>
            </div>
          </div>
        </header>

        <!-- Excerpt -->
        <div
          v-html="createExcerpt(post.excerpt, 150)"
          class="post-excerpt"
        ></div>

        <!-- Read more link -->
        <div class="read-more">
          <NuxtLink :to="post.uri" class="button button-primary button-small">
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
@use "@/assets/scss/components/post-card";
</style>
