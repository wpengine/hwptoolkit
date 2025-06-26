<script setup>
import NotFound from '../components/404.vue'

const INDEX_QUERY = gql`
  query indexTemplateNodeQuery($uri: String!) {
    nodeByUri(uri: $uri) {
      __typename
      uri
      id
      ... on NodeWithTitle {
        title
      }
      ... on NodeWithContentEditor {
        content
      }
    }
  }
`;

const uri = useRoute().path || '/'
const { data, loading, error } = useGraphQL(INDEX_QUERY, { slug: uri });
const node = computed(() => data.indexTemplateNodeQuery.response.data.nodeByUri || null);

</script>

<template>
  <div class="container">
    <div v-if="node">
      <h1 class="">{{ node.title || 'Untitled' }}</h1>
      <div v-if="node.content" class="" v-html="node.content"></div>
      <div v-else class="">No content available.</div>
    </div>
    <div v-else="">
      <NotFound />
    </div>
  </div>
</template>