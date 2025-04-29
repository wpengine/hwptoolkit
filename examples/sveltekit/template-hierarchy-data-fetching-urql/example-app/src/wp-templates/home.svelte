<script lang="ts" module>
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      query: gql`
        query {
          posts(first: 6) {
            nodes {
              id
              title
              uri
              excerpt
            }
          }
        }
      `,
      variables: (event: LoadEvent) => null,
    },
  ];
</script>

<script>
  const { data } = $props();
</script>

<main id="home">
  <h1>My WP + Astro Blog!</h1>

  <p>I like sharing my life!</p>

  <section id="recent-posts">
    <h2>Recent Posts</h2>
    <div class="post-grid">
      {#each data.posts.nodes as post}
        <div data-key={post.id} class="post">
          <h3 class="post-title">{post.title}</h3>
          <div class="post-excerpt">{@html post.excerpt}</div>
          <a class="post-link" href={post.uri}> Read more... </a>
        </div>
      {/each}
    </div>
  </section>
</main>
