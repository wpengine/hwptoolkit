<script module lang="ts">
  import { gql } from "$lib/client";
  import type { TemplateQuery } from "$lib/queryHandler";
  import NavStructure from "./NavStructure.svelte";

  export const query: TemplateQuery = {
    paginate: (data) => data.menu.menuItems.pageInfo,
    query: gql`
      query headerNavQuery($after: String = null) {
        menu(id: "primary", idType: LOCATION) {
          menuItems(first: 10, after: $after) {
            pageInfo {
              hasNextPage
              endCursor
            }
            nodes {
              id
              label
              uri
              parentId
            }
          }
        }
      }
    `,
  };
</script>

<script>
  import { flatListToHierarchical } from "$lib/wpgraphql";

  import { page } from "$app/state";

  const menu = $derived(
    flatListToHierarchical(
      page.data.layoutData.headerNavQuery.response.data.menu.menuItems.nodes
    )
  );
</script>

<nav>
  <ul>
    {#each menu as item (item.id)}
      <NavStructure navItem={item} />
    {/each}
  </ul>
</nav>

<style>
  nav {
    background: #eee;
    padding: 1rem;
  }
  nav ul {
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
  }

  :global {
    nav a {
      text-decoration: none;
      color: #333;
    }
    nav a:hover {
      text-decoration: underline;
    }
    nav a:visited {
      color: #666;
    }
    nav a:active {
      color: #000;
    }
    nav a:focus {
      outline: 2px solid #000;
    }
    nav a:focus-visible {
      outline: 2px solid #000;
    }
    nav a:focus:not(:focus-visible) {
      outline: none;
    }
  }
</style>
