import React from 'react';

const blogPosts = [
    {
        id: 1,
        title: 'Understanding React Hooks',
        summary: 'A deep dive into the world of React Hooks and how they can simplify your code.',
        date: '2023-10-01'
    },
    {
        id: 2,
        title: 'JavaScript ES6 Features',
        summary: 'An overview of the most important features introduced in ES6.',
        date: '2023-09-15'
    },
    {
        id: 3,
        title: 'CSS Grid vs Flexbox',
        summary: 'Comparing CSS Grid and Flexbox for building modern web layouts.',
        date: '2023-08-30'
    }
];

const BlogPage = () => {
    return (
        <div>
            <h1>Blog Listings</h1>
            <ul>
                {blogPosts.map(post => (
                    <li key={post.id}>
                        <h2>{post.title}</h2>
                        <p>{post.summary}</p>
                        <small>{post.date}</small>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default BlogPage;
