/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  async redirects() {
    return [
      {
        source: "/sitemap",
        destination: "/sitemap.xml",
        permanent: true,
      },
    ];
  },
};

export default nextConfig;
