<script lang="ts" module>
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      query: gql`
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
      `,
      variables: (event: LoadEvent) => ({ uri: event.params.uri || "/" }),
    },
  ];
</script>

<script>
  const { data } = $props();

  const node = $derived(data.indexTemplateNodeQuery.response.data.nodeByUri);
</script>

<p>
  This is the <strong>index</strong> template for the WordPress template hierarchy.
  It will be used to render the WordPress content if no more appropriate template
  is provided (e.g. front-page, single, singular, archive, etc). It should never
  be used directly.
</p>

<hr />

{#if node?.title}
  <h1>{@html node.title}</h1>
{/if}
{#if node?.content}
  <div>{@html node.content}</div>
{/if}
{#if !node?.content && !node?.title}
  <pre>
    <code>{JSON.stringify(node, null, 2)}</code>
	</pre>
{/if}

<style>
  pre {
    margin: 0;
    padding: 0;
  }
</style>
