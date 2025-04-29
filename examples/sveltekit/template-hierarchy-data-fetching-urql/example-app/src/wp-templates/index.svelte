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
      variables: (event: LoadEvent) => ({ uri: event.params.uri }),
    },
  ];
</script>

<script>
  const { data } = $props();
</script>

<p>
  This is the <strong>index</strong> template for the WordPress template hierarchy.
  It will be used to render the WordPress content if no more appropriate template
  is provided (e.g. front-page, single, singular, archive, etc). It should never
  be used directly.
</p>

{#if data.nodeByUri?.title}
  <h1>{@html data.nodeByUri.title}</h1>
{/if}
{#if data.nodeByUri.content}
  <div>{@html data.nodeByUri.content}</div>
{/if}
{#if !data.nodeByUri.content && !data.nodeByUri.title}
  <pre>
    <code>{JSON.stringify(data ?? {}, null, 2)}</code>
	</pre>
{/if}

<style>
  pre {
    margin: 0;
    padding: 0;
  }
</style>
