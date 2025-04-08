<script setup>
import { ref } from "vue";
import Post from "~/components/wp/Post.vue";
import TheHeader from "~/components/wp/TheHeader.vue";

const config = useRuntimeConfig();
const error = ref(null);

const query = `
  query {
    posts {
      nodes {
        title
        date
        excerpt
        uri
      }
    }
  }
`;

const { data, pending } = await useFetch(
  `${config.public.wordpressUrl}?query=${encodeURIComponent(query)}`,
  {
    method: "GET",
    transform: (response) => response?.data?.posts?.nodes || [],
  }
);
</script>

<template>
  <div>
    <TheHeader />
    <div class="grid gap-8 grid-cols-1 lg:grid-cols-3 p-6">
      <template v-if="!pending && data">
        <Post v-for="post in data" :key="post.uri" :post="post" />
      </template>
      <div v-else-if="error" class="text-red-500">Error: {{ error }}</div>
      <div v-else>Loading...</div>
    </div>
  </div>
</template>
