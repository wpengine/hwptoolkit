<template>
  <div>
    <div class="grid grid-cols-4 gap-5">
      <div v-for="p in products" :key="p.id">
        <ProductCard :product="p" />
      </div>
    </div>
  </div>
</template>

<script setup>
import ProductCard from "~/components/product/ProductCard.vue";

// Fetch the products
const { data: products } = await useFetch("https://fakestoreapi.com/products");

definePageMeta({
  layout: "products",
});
</script>
