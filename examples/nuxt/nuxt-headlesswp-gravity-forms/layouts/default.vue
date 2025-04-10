<template>
  <div>
    <header class="shadow-sm bg-white">
      <nav class="container mx-auto p-4 flex justify-between">
        <NuxtLink to="/" class="font-bold">Nuxt Headless WP Demo</NuxtLink>
        <ul class="flex gap-4">
          <li><NuxtLink to="/">Home</NuxtLink></li>
          <li><NuxtLink to="/wpblog">blog</NuxtLink></li>

          <li><NuxtLink to="/questionnaire">Questionnaire</NuxtLink></li>
          <li><NuxtLink to="/products" class="btn">products</NuxtLink></li>
        </ul>
      </nav>
    </header>
    <div class="container mx-auto p-4">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.router-link-exact-active {
  color: #12b488;
}
</style>
