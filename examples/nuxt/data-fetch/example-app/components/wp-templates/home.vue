<script setup>
import { computed } from "vue";
import { useGraphQL, gql } from "../../lib/client";
import PostListing from "../templates/listing/Post.vue";
import Loading from "../Loading.vue";

const HOME_SETTINGS_QUERY = gql`
  query HomeSettingsQuery {
    generalSettings {
      title
      description
    }
  }
`;

const HOME_BLOG_POSTS_QUERY = gql`
  query HomeBlogPostsQuery {
    posts(first: 4) {
      nodes {
        id
        title
        date
        uri
        slug
        excerpt
        featuredImage {
          node {
            sourceUrl
            altText
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
        categories {
          nodes {
            name
            slug
          }
        }
      }
    }
  }
`;

// Use unique keys for proper SSR state management
const {
  data: blogData,
  loading: blogLoading,
  error: blogError,
} = useGraphQL(
  HOME_BLOG_POSTS_QUERY,
  {},
  {
    key: "home-blog-posts-unique",
    loadingText: "Loading recent posts...",
  }
);

const {
  data: settingsData,
  loading: settingsLoading,
  error: settingsError,
} = useGraphQL(
  HOME_SETTINGS_QUERY,
  {},
  {
    key: "home-settings-unique",
    loadingText: "Loading site information...",
  }
);

// Computed properties with consistent fallbacks
const posts = computed(() => {
  return blogData.value?.posts?.nodes || [];
});

const siteInfo = computed(() => {
  const title = settingsData.value?.generalSettings?.title;
  const description = settingsData.value?.generalSettings?.description;

  return {
    title: title,
    description: description || "Welcome to my site",
  };
});

// Prevent hydration mismatches by ensuring consistent initial state
const isClient = computed(() => process.import.meta.client);
</script>

<template>
  <main>
    <section id="hero">
      <!-- Always render the same structure to prevent hydration mismatches -->
      <template v-if="settingsLoading">
        <Loading text="Loading site information..." />
      </template>

      <template v-else-if="settingsError">
        <div>
          <h1>My WordPress Site1</h1>
          <p>Welcome to my site</p>
          <small>Error loading site data: {{ settingsError.message }}</small>
        </div>
      </template>

      <template v-else>
        <div>
          <h1>{{ siteInfo.title }}</h1>
          <p>{{ siteInfo.description }}</p>
        </div>
      </template>
    </section>

    <div class="container">
      <section id="recent-posts">
        <h2>Recent Posts</h2>

        <template v-if="blogError">
          <div>
            <p>Failed to load posts</p>
            <p>{{ blogError.message }}</p>
          </div>
        </template>

        <template v-else-if="blogLoading">
          <Loading text="Loading recent posts..." />
        </template>

        <template v-else-if="posts.length === 0">
          <div>
            <p>No recent posts found.</p>
          </div>
        </template>

        <template v-else>
          <PostListing :posts="posts" :loading="false" :cols="4" />
        </template>
      </section>

      <div class="text-center">
        <NuxtLink to="/blog" class="button button-primary button-large">
          View All Blog Posts →
        </NuxtLink>
      </div>
    </div>
  </main>
</template>

<style scoped lang="scss">
@use "@/assets/scss/pages/home";
</style>
