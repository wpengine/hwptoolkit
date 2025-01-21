'use client'

import { usePathname } from 'next/navigation';

// Copied from template-hierarchy.js
export const templateHierarchy = {
    "author_archive": ["author-$nicename", "author-$id", "author", "archive", "index"],
    "category_archive": ["category-$slug", "category-$id", "category", "archive", "index"],
    "custom_post_type_archive": ["archive-$post_type", "archive", "index"],
    "custom_taxonomy_archive": ["taxonomy-$taxonomy-$term", "taxonomy-$taxonomy-$slug", "taxonomy-$taxonomy-$id", "taxonomy-$taxonomy", "taxonomy", "archive", "index"],
    "date_archive": ["date", "archive", "index"],
    "tag_archive": ["tag-$slug", "tag-$id", "tag", "archive", "index"],
    "attachment_post": ["attachment-$mime_type", "attachment", "single", "singular", "index"],
    "custom_post": ["single-$post_type-$slug", "single-$post_type-$id", "single-$post_type", "single", "singular", "index"],
    "blog_post": ["single-post_type-$slug", "single-post", "single", "singular", "index"],
    "page": ["page-$slug", "page-$id", "page", "singular", "index"],
    "front_page": ["front-page", "home", "page", "singular", "index"],
    "home": ["home", "index"],
    "404": ["404", "index"],
    "search": ["search", "index"]
};

export default function IndexPage() {
    const pathname = usePathname();
    const slug = pathname.substring(pathname.lastIndexOf('/') + 1);

    const availablePaths = Object.keys(templateHierarchy);

    return (
        <div className="container mx-auto p-6">
            <h1 className="text-4xl font-extrabold mb-8 mt-16 text-center">Index Page - {slug}</h1>
            <p className="text-lg mb-6 text-center">This is a fallback page if no template existed.</p>
            <ul className="space-y-4">
                {availablePaths.map((path) => (
                    <li key={path} className="rounded-lg p-4">
                        <strong className="text-xl">{path}</strong> - <span className="text-green-500">{templateHierarchy[path].join(', ')}</span>
                    </li>
                ))}
            </ul>
        </div>
    );
}
