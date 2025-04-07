/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  images: {
    // Allow images from localhost.
    // Please change this to your domain if you are using a different domain.
    domains: ["localhost"],
  },
  env: {
    // A list of sites to be used in the app. See src/lib/client.js for more info.
    WORDPRESS_SITES: JSON.stringify({
      main: process.env.NEXT_PUBLIC_WORDPRESS_URL + "/graphql",
      movie_site: process.env.NEXT_PUBLIC_MOVIE_WORDPRESS_URL + "/graphql",
    }),
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
