<script setup>
import { computed } from 'vue';
import { useGraphQL, gql } from '../../lib/client';
import PostListing from '../templates/listing/Post.vue';
import Loading from '../Loading.vue';
import EmptyState from '../EmptyState.vue';
const props = defineProps({
  templateData: Object
});
const archiveQuery = gql`
  query ArchiveTemplateNodeQuery($uri: String!) {
  archive: nodeByUri(uri: $uri) {
    __typename

    ... on User {
      contentNodes: posts {
        nodes {
          id
          uri
          title
          excerpt
          date
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
          ... on Post {
            categories {
              nodes {
                name
                slug
              }
            }
            tags {
              nodes {
                name
                slug
              }
            }
          }
        }
      }
    }

    ... on TermNode {
      name
      description
    }

    ... on Tag {
      name
      description
      contentNodes {
        nodes {
          id
          uri
          date
          ... on NodeWithTitle {
            title
          }
          ... on NodeWithExcerpt {
            excerpt
          }
          ... on NodeWithFeaturedImage {
            featuredImage {
              node {
                sourceUrl
                altText
              }
            }
          }
          ... on Post {
            categories {
              nodes {
                name
                slug
              }
            }
            tags {
              nodes {
                name
                slug
              }
            }
          }
        }
      }
    }

    ... on Category {
      name
      description
      contentNodes {
        nodes {
          id
          uri
          date
          ... on NodeWithTitle {
            title
          }
          ... on NodeWithExcerpt {
            excerpt
          }
          ... on NodeWithFeaturedImage {
            featuredImage {
              node {
                sourceUrl
                altText
              }
            }
          }
          ... on Post {
            categories {
              nodes {
                name
                slug
              }
            }
            tags {
              nodes {
                name
                slug
              }
            }
          }
        }
      }
    }
  }
}

`;
const uri = useRoute().path || '/'
const { data, loading, error } = useGraphQL(archiveQuery, { uri: props.templateData?.uri || uri }, {
  key: `archive-${props.templateData?.uri || uri}`,
  loadingText: 'Loading archive...'
});

const archive = computed(() => data.value?.archive || null);
</script>

<template>
  <main class="container">

    <!-- Loading state -->
    <div v-if="loading" class="text-center">
      <Loading />
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="text-center">
      <h1 class="">Error</h1>
      <p>{{ error.message }}</p>
    </div>

    <!-- Archive content -->
    <div v-else-if="archive">
      <header class="mb-4 text-center">
        <h1 class="">{{ archive.name }}</h1>
        <div v-if="archive.description" class="" v-html="archive.description"></div>
      </header>

      <template v-if="archive.contentNodes.nodes">
        <PostListing :posts="archive.contentNodes.nodes" :loading="loading" :cols="3" />
      </template>

      <!-- Empty state -->
      <div v-else class="text-center">
        <EmptyState
          text="There are no posts available in this archive."
        />
      </div>
    </div>

    <!-- Fallback -->
    <div v-else class="py-10 text-center">
      <h1 class="text-2xl font-bold mb-2">Archive Not Found</h1>
      <p>The requested archive could not be found.</p>
      <NuxtLink to="/" class="text-blue-500 hover:underline mt-4 inline-block">
        Return home
      </NuxtLink>
    </div>
  </main>
</template>