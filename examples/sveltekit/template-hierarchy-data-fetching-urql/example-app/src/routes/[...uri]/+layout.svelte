<script lang="ts">
  import TemplateHierarchyInfo from "$components/TemplateHierarchyInfo.svelte";
  import Nav from "$components/Nav.svelte";
  import type { LayoutProps } from "./$types";

  const { children }: LayoutProps = $props();
</script>

<header>
  <TemplateHierarchyInfo />

  <Nav />
</header>

<div class="container">
  {@render children()}
</div>

<style>
  .container {
    padding: 0 1rem;
    background-color: white;
  }
</style>
