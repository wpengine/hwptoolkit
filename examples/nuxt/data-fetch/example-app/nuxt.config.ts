export default defineNuxtConfig({
  runtimeConfig: {
    // Private keys are only available on the server
    apiSecret: "your-secret-key",

    // Public keys that are exposed to the client
    public: {
      wordpressUrl: process.env.WORDPRESS_URL || "http://localhost:8890",
    },
  },
   experimental: {
    componentIslands: true
  },
  // Add CSS global files
  css: [
    // Add your global SCSS file
    '@/assets/scss/global.scss'
  ],
  
  vite: {
    css: {
      preprocessorOptions: {
        scss: {
          // Fix: Use @use instead of @import and remove "as *"
          additionalData: '@use "@/assets/scss/_variables.scss";'
        }
      }
    }
  }
});