export interface Post {
  id: string;
  databaseId?: number;
  title: string;
  content?: string;
  date: string;
  uri: string;
  excerpt: string;
  slug?: string;
  featuredImage?: FeaturedImage;
  author?: Author;
  categories?: {
    nodes: Category[];
  };
  tags?: {
    nodes: Tag[];
  };
}

export interface Author {
  node: {
    name: string;
    avatar?: {
      url: string;
    };
  };
}

export interface Category {
  name: string;
  slug: string;
}

export interface Tag {
  name: string;
  slug: string;
}

export interface FeaturedImage {
  node: {
    sourceUrl: string;
    altText?: string;
  };
}

export interface PageInfo {
  hasNextPage: boolean;
  endCursor: string | null;
}

export interface PostsResponse {
  posts: {
    pageInfo: PageInfo;
    edges: Array<{
      cursor: string;
      node: Post;
    }>;
  };
}
