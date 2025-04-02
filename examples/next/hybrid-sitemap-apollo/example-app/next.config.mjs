/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  async rewrites() {
    return [
      {
        source: "/:sitemap(wp-sitemap-.*).xml",
        destination: "/api/sitemap?sitemap=:sitemap",
      },
    ];
  },
};

export default nextConfig;
