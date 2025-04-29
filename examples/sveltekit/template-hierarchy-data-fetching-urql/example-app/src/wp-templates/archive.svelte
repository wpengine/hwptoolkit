<script lang="ts" module>
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      name: "ArchiveTemplateNodeQuery",
      stream: false,
      query: gql`
        query ArchiveTemplateNodeQuery($uri: String!) {
          archive: nodeByUri(uri: $uri) {
            __typename
            ... on TermNode {
              name
              description
            }
            ... on Tag {
              contentNodes {
                nodes {
                  ... on NodeWithTitle {
                    title
                  }
                  uri
                }
              }
            }
            ... on Category {
              contentNodes {
                nodes {
                  ... on NodeWithTitle {
                    title
                  }
                  uri
                }
              }
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

<main>
  <h1>{data.archive.name}</h1>
  <p>{@html data.archive.description}</p>
  <ol>
    {#each data.archive.contentNodes.nodes as content}
      <li>
        <a href={content.uri}>{@html content.title}</a>
      </li>
    {/each}
  </ol>
</main>
