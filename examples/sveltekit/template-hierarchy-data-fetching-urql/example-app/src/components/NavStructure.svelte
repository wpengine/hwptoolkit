<script>
  import Self from "./NavStructure.svelte";
  let { navItem } = $props();

  const hasChildren = $derived(Boolean(navItem?.children?.length > 0));
</script>

<li>
  <a href={navItem?.uri}>
    {navItem?.label}
  </a>
  {#if hasChildren}
    <ul>
      {#each navItem.children as child (child.id)}
        <Self navItem={child} />
      {/each}
    </ul>
  {/if}
</li>
