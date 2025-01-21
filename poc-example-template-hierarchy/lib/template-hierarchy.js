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
}
