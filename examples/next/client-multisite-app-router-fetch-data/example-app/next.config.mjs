/** @type {import('next').NextConfig} */
const nextConfig = {
    reactStrictMode: true,
    images: {
      // Allow images from localhost.
      // Please change this to your domain if you are using a different domain.
      domains: ["localhost"],
    },
    // Note: env variables are set in next.config.js only accept string values so used publicRuntimeConfig instead
    publicRuntimeConfig: {
      // Controls posts per page for blog, category and tag pages
      wordPressDisplaySettings: {
        postsPerPage: 5,
      },
    },
  };
  
  export default nextConfig;
  