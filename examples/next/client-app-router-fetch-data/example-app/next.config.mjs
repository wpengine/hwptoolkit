/** @type {import('next').NextConfig} */
const nextConfig = {
    reactStrictMode: true,
    wordPressDisplaySettings: {
        // Controls posts per page for blog, category and tag pages
        postsPerPage: 5,
    }
};

// Allow images from localhost. 
// Please change this to your domain if you are using a different domain.
nextConfig.images = {
    domains: ['localhost'],
};

export default nextConfig;
