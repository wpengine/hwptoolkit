/** @type {import('next').NextConfig} */
const nextConfig = {
    reactStrictMode: true,
};

// Allow images from localhost. 
// Please change this to your domain if you are using a different domain.
nextConfig.images = {
    domains: ['localhost'],
};

export default nextConfig;
