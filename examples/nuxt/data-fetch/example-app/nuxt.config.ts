export default defineNuxtConfig({
  runtimeConfig: {
    // Private keys are only available on the server
    apiSecret: "your-secret-key",

    // Public keys that are exposed to the client
    public: {
      wordpressUrl: process.env.WORDPRESS_URL || "http://localhost:8890",
    },
  },
});
