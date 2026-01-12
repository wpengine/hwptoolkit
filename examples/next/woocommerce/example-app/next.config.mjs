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
      // ✅ Gravatar (for user avatars)
      {
        protocol: "https",
        hostname: "secure.gravatar.com",
        pathname: "/avatar/**",
      },
      {
        protocol: "https",
        hostname: "gravatar.com",
        pathname: "/avatar/**",
      },
      {
        protocol: "http",
        hostname: "gravatar.com",
        pathname: "/avatar/**",
      },
      // ✅ Common image hosting services
      {
        protocol: "https",
        hostname: "images.unsplash.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "via.placeholder.com",
        pathname: "/**",
      },
      // ✅ WordPress.com hosted images
      {
        protocol: "https",
        hostname: "*.wp.com",
        pathname: "/**",
      },
      // ✅ Add your specific WordPress site domains
      {
        protocol: "https",
        hostname: "*.wordpress.com",
        pathname: "/**",
      },
    ],
    // ✅ Optional: Add image optimization settings
    formats: ['image/webp', 'image/avif'],
    minimumCacheTTL: 60,
    dangerouslyAllowSVG: true,
    contentSecurityPolicy: "default-src 'self'; script-src 'none'; sandbox;",
  },
};

export default nextConfig;