export interface Page {
  id: string;
  databaseId?: number; 
  title: string;
  content?: string;
  date: string;
  uri: string;
  excerpt: string;
  slug?: string;
  commentsCount: number;
}