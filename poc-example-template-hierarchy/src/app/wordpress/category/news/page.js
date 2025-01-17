import React from 'react';

const NewsPage = () => {
    return (
        <div>
            <h1 className="text-3xl font-bold mb-12 mt-24">News Page</h1>
            <p className="text-lg mb-6">This is an individual page for <code className='text-green-700'>category-$slug</code> for news.</p>
            <ul className="list-disc pl-5 space-y-2">
                <li className="text-base">News Post 1</li>
                <li className="text-base">News Post 2</li>
                <li className="text-base">News Post 3</li>
            </ul>
        </div>
    );
};

export default NewsPage;
