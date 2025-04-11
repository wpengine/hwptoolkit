/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  async rewrites() {
    return [
      {
        source: "/:path(wp-sitemap-.*).xml",
        destination: "/api/proxy-sitemap/:path",
      },
      {
        source: "/sitemap.xml",
        destination: "/api/proxy-sitemap/sitemap.xml",
      },
    ];
  },
};

export default nextConfig;
