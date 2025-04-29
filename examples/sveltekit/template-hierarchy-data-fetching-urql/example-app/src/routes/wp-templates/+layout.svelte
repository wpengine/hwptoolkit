<script lang="ts">
  let { children } = $props();
</script>

<nav>
  <a href="/">Home</a>
  <a href="/hello-world">Blog Post</a>
  <a href="/category/category-1">Category 1</a>
  <a href="/sample-page">Sample Page</a>
  <a href="/not-wordpress-example">Not WP</a>
</nav>

{@render children()}
