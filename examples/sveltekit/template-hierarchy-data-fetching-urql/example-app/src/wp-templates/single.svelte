<script module lang="ts">
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      stream: true,
      query: gql`
        query postAuthorData($uri: String!) {
          post: nodeByUri(uri: $uri) {
            id
            ... on NodeWithAuthor {
              author {
                node {
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
                  id
                  name
                  uri
                }
              }
              tags {
                nodes {
                  id
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

  const post = $derived(data.singleTemplatePageQuery.response.data.post);
  const authorPromise = $derived(data.postAuthorData.response);
</script>

<main>
  <article>
    <h1>{@html post.title}</h1>
    <aside>
      {#await authorPromise}
        <p>Loading author data...</p>
      {:then { data: { post: { author: { node: author } } } }}
        <p>
          <strong>Author:</strong>
          <a href={author.uri}>{author.name}</a>
        </p>
      {:catch error}
        <p>Failed to load author data: {error.message}</p>
      {/await}
    </aside>
    <div>{@html post.content}</div>
    <footer>
      <span class="term-section">
        <strong>Categories:</strong>
        <span class="term-list">
          {#if post.categories.nodes?.length > 0}
            {#each post.categories.nodes as category (category.id)}
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
          {#if post.tags.nodes?.length > 0}
            {#each post.tags.nodes as tag (tag.id)}
              <span> <a href={tag.uri}>{@html tag.name} </a></span>
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
