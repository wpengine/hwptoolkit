// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  modules: ["@nuxtjs/tailwindcss"],
  runtimeConfig: {
    public: {
      wordpressUrl: "",
    },
  },
  routeRules: {
    // Blog listing page - revalidates every 60 seconds
    "/wpblog": { isr: 60 },
    // Individual blog posts - cached until next deployment
    "/wpblog/**": { isr: true },
    // Pre-render the form page at build time
    "/headlesswp-gform": { prerender: true },
  },
});
