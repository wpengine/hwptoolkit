<script module lang="ts">
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      query: gql`
        query singleTemplatePageQuery($uri: String!) {
          post: nodeByUri(uri: $uri) {
            id
            uri
            ... on NodeWithTitle {
              title
            }
            ... on NodeWithContentEditor {
              content
            }
            ... on Post {
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
            }
          }
        }
      `,
      variables: (event: LoadEvent) => {
        return {
          uri: event.params.uri,
        };
      },
    },
  ];
</script>

<script>
  let { data } = $props();
</script>

<main>
  <article>
    <h1>{@html data.post.title}</h1>
    <div>{@html data.post.content}</div>
    <footer>
      <span class="term-section">
        <strong>Categories:</strong>
        <span class="term-list">
          {#if data.post.categories.nodes?.length > 0}
            {#each data.post.categories.nodes as category}
              <span>
                <a href={category.uri}>{category.name}</a>
              </span>
            {/each}
          {:else}
            <span>None.</span>
          {/if}
        </span>
      </span>
      <span class="term-section">
        <strong>Tags: </strong>
        <span class="term-list">
          {#if data.post.tags.nodes?.length > 0}
            {#each data.post.tags.nodes as category}
              <span> <a href={category.uri}>{@html category.name} </a></span>
            {/each}
          {:else}
            <span>None.</span>
          {/if}
        </span>
      </span>
    </footer>
  </article>
</main>

<style>
  .term-list {
    display: flex;
    flex-direction: row;
    gap: 0.5rem;
  }

  .term-section {
    margin: 0.5rem 0;
    display: flex;
    flex-direction: row;
    gap: 0.5rem;
  }
</style>
