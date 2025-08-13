/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  images: {
    remotePatterns: [
      // Dev
      {
        protocol: "http",
        hostname: "localhost",
        port: "8890",
        pathname: "/wp-content/uploads/**",
      },
      {
        protocol: "http",
        hostname: "127.0.0.1",
        port: "8890",
        pathname: "/wp-content/uploads/**",
      },
      // ✅ Production
      {
        protocol: "https",
        hostname: "your-production-site.com",
        pathname: "/wp-content/uploads/**",
      },
    ],
  },
};

export default nextConfig;
