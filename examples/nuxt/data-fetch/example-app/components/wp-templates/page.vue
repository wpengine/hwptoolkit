<script setup>
import { computed } from "vue";
import { useGraphQL, gql } from "../../lib/client";
import Comments from "../Comments.vue";
import Loading from "../Loading.vue";

const PAGE_QUERY = gql`
  query GetPage($slug: ID!) {
    page(id: $slug, idType: URI) {
      id
      databaseId
      title
      content
      commentCount
      commentStatus
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
const { data, loading, error } = useGraphQL(PAGE_QUERY, { slug: uri });

const page = computed(() => data.value?.page || null);
const pageId = computed(() => page.value?.databaseId || null);

// Check if comments are enabled for this page
const commentsEnabled = computed(
  () => page.value?.commentStatus === "open" || page.value?.commentCount > 0 // Show section if there are existing comments
);
</script>

<template>
  <div class="container">
    <!-- Loading state -->
    <div v-if="loading" class="py-10 text-center">
      <Loading />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="py-10 text-center">
      <h1 class="text-2xl font-bold text-red-500 mb-2">Error</h1>
      <p>{{ error.message }}</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return to homepagee
      </NuxtLink>
    </div>

    <!-- Page content -->
    <article v-else-if="page" class="single-page" id="page-{{ pageId }}">
      <h1 class="text-center">{{ page.title }}</h1>

      <!-- Featured image -->
      <img
        v-if="page.featuredImage?.node"
        :src="page.featuredImage.node.sourceUrl"
        :alt="page.featuredImage.node.altText || page.title"
        class=""
      />

      <!-- Page content -->
      <div class="content" v-html="page.content"></div>

      <!-- Comments section - only show if comments are enabled -->
      <Comments
        v-if="commentsEnabled && pageId"
        :post-id="pageId"
        content-type="page"
      />

      <!-- Message if comments are closed but exist -->
      <div
        v-else-if="page.commentCount > 0 && page.commentStatus !== 'open'"
        class="mt-12 pt-8 border-t border-gray-200 text-center text-gray-500"
      >
        <p>Comments are closed for this page.</p>
      </div>
    </article>
  </div>
</template>

<style scoped lang="scss">
@use "@/assets/scss/pages/page";
</style>
