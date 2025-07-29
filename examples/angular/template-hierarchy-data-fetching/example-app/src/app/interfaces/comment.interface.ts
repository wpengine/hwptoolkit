export interface Comment {
  id: string;
  content: string;
  date: string;
  author: CommentAuthor;
  parentId?: string;
  replies?: Comment[];
}

export interface CommentAuthor {
  node: {
    name: string;
    url?: string;
    avatar?: {
      url: string;
    };
  };
}

export interface ReplyData {
  author: string;
  parentId: string;
}

export interface CommentPageInfo {
  hasNextPage: boolean;
  endCursor: string | null;
}

export interface CommentFormData {
  author: string;
  email: string;
  url: string;
  content: string;
}
export interface CommentResponse {
  comments: {
    pageInfo: {
      hasNextPage: boolean;
      endCursor: string | null;
    };
    edges: {
      cursor: string;
      node: Comment;
    }[];
  };
}
