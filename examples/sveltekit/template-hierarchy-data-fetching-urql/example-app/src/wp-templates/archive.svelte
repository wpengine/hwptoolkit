<script lang="ts" module>
  import { gql } from "$lib/client";
  import type { LoadEvent } from "@sveltejs/kit";

  export const queries = [
    {
      name: "archiveQuery",
      stream: false,
      query: gql`
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
                }
              }
            }
            ... on TermNode {
              name
              description
            }
            ... on Tag {
              contentNodes {
                nodes {
                  id
                  ... on NodeWithTitle {
                    title
                  }
                  ... on NodeWithExcerpt {
                    excerpt
                  }
                  uri
                }
              }
            }
            ... on Category {
              contentNodes {
                nodes {
                  id

                  ... on NodeWithTitle {
                    title
                  }
                  ... on NodeWithExcerpt {
                    excerpt
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

  const archive = $derived(data.archiveQuery.response.data.archive);
</script>

<main>
  <h1>{archive.name}</h1>
  <p>{@html archive.description}</p>
  <ol>
    {#each archive.contentNodes?.nodes as content (content.id)}
      <li>
        <a href={content.uri}>{@html content.title}</a>

        <p class="excerpt">
          {@html content.excerpt}
        </p>
      </li>
    {/each}
  </ol>
</main>
