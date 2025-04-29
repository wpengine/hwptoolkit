<script lang="ts">
  import TemplateHierarchyInfo from "$components/TemplateHierarchyInfo.svelte";
  import Nav from "$components/Nav.svelte";

  let { children } = $props();
</script>

<TemplateHierarchyInfo />
<Nav />

<div class="container">
  {@render children()}
</div>

<style>
  .container {
    padding: 0 1rem;
    background-color: white;
  }
</style>
