import {
  Component,
  Input,
  OnInit,
  signal,
  computed,
  effect,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { CommentFormComponent } from './comment-form/comment-form.component';
import { CommentThreadComponent } from './comment-thread/comment-thread.component';
import { GraphQLService, gql } from '../../utils/graphql.service';
import { EmptyStateComponent } from '../empty-state/empty-state.component';
import { LoadingComponent } from '../loading/loading.component';
interface CommentAuthor {
  node: {
    name: string;
    url?: string;
    avatar?: {
      url: string;
    };
  };
}

interface Comment {
  id: string;
  content: string;
  date: string;
  author: CommentAuthor;
  parentId?: string;
  replies?: Comment[];
}

interface PageInfo {
  hasNextPage: boolean;
  endCursor: string | null;
}

interface CommentData {
  id: string;
  commentCount: number;
  comments: {
    pageInfo: PageInfo;
    nodes: Comment[];
  };
}

interface ReplyData {
  author: string;
  parentId: string;
}

@Component({
  selector: 'app-comments',
  standalone: true,
  imports: [
    CommonModule,
    CommentFormComponent,
    CommentThreadComponent,
    LoadingComponent,
    EmptyStateComponent,
  ],
  templateUrl: './comments.component.html',
  styleUrls: ['./comments.component.scss'],
})
export class CommentsComponent implements OnInit {
  @Input() postId!: number;
  @Input() contentType: string = 'post';
  @Input() commentsPerPage: number = 10;

  // Reactive state using signals
  private data = signal<any>(null);
  loading = signal<boolean>(true);
  error = signal<any>(null);

  // Additional state for pagination
  allComments = signal<Comment[]>([]);
  pageInfo = signal<PageInfo>({ hasNextPage: false, endCursor: null });
  loadingMore = signal<boolean>(false);

  // Reply and form state
  replyData = signal<ReplyData | null>(null);
  showCommentForm = signal<boolean>(true);

  // Computed properties
  content = computed(() => this.data()?.[this.contentType] || null);

  comments = computed(() => {
    // Simply return allComments - no side effects allowed in computed
    return this.allComments();
  });

  commentCount = computed(() => this.content()?.commentCount || 0);

  threadedComments = computed(() => {
    return this.buildCommentTree(this.comments());
  });

  constructor(private graphqlService: GraphQLService) {}

  ngOnInit(): void {
    this.loadInitialComments();
  }

  /**
   * Load initial comments
   */
  private async loadInitialComments(): Promise<void> {
    try {
      this.loading.set(true);
      this.error.set(null);

      // Use actual GraphQL service to load comments
      await this.loadComments();
    } catch (error) {
      console.error('Error loading comments:', error);
      this.error.set(error);
    } finally {
      this.loading.set(false);
    }
  }

  /**
   * Load comments using GraphQL service
   */
  private async loadComments(): Promise<void> {
    const query = this.getCommentsQuery(this.contentType);
    const variables = {
      postId: this.postId,
      first: this.commentsPerPage,
      after: null, // Always null for initial load
    };

    console.log('📤 Loading comments with variables:', variables);

    return new Promise((resolve, reject) => {
      this.graphqlService.query<any>(query, variables).subscribe({
        next: (response) => {
          console.log('✅ Comments loaded successfully:', response);

          // Set the data for computed properties
          this.data.set(response);

          // Extract comments data and update signals
          const contentData = response[this.contentType];
          if (contentData?.comments?.nodes) {
            // Set initial comments
            this.allComments.set([...contentData.comments.nodes]);
            this.pageInfo.set(contentData.comments.pageInfo);
          }

          resolve();
        },
        error: (error) => {
          console.error('❌ Error loading comments:', error);
          this.error.set(error);
          reject(error);
        },
      });
    });
  }

  /**
   * Get the GraphQL query for comments
   */
  private getCommentsQuery(contentType: string): string {
    return gql`
      query GetComments($postId: ID!, $first: Int, $after: String) {
        ${contentType}(id: $postId, idType: DATABASE_ID) {
          id
          commentCount
          comments(first: $first, after: $after, where: {orderby: COMMENT_DATE}) {
            pageInfo {
              hasNextPage
              endCursor
            }
            nodes {
              id
              content
              date
              author {
                node {
                  name
                  url
                  avatar {
                    url
                  }
                }
              }
              parentId
            }
          }
        }
      }
    `;
  }

  /**
   * Recursive function to build nested comment structure
   */
  private buildCommentTree(
    comments: Comment[],
    parentId: string | null = null
  ): Comment[] {
    return comments
      .filter((comment) => comment.parentId === parentId)
      .map((comment) => ({
        ...comment,
        replies: this.buildCommentTree(comments, comment.id),
      }));
  }

  /**
   * Load more comments (pagination)
   */
  async loadMoreComments(): Promise<void> {
    if (!this.pageInfo().hasNextPage || this.loadingMore()) {
      return;
    }

    this.loadingMore.set(true);

    try {
      const query = this.getCommentsQuery(this.contentType);
      const variables = {
        postId: this.postId,
        first: this.commentsPerPage,
        after: this.pageInfo().endCursor,
      };

      console.log('📤 Loading more comments with variables:', variables);

      return new Promise((resolve, reject) => {
        this.graphqlService.query<any>(query, variables).subscribe({
          next: (response) => {
            console.log('✅ More comments loaded successfully:', response);

            const moreCommentsData = response[this.contentType];

            if (moreCommentsData?.comments?.nodes) {
              const newComments = moreCommentsData.comments.nodes;

              // Add new comments to existing ones
              this.allComments.update((existing) => [
                ...existing,
                ...newComments,
              ]);

              // Update pagination info
              this.pageInfo.set(moreCommentsData.comments.pageInfo);
            }

            resolve();
          },
          error: (error) => {
            console.error('❌ Error loading more comments:', error);
            this.error.set(error);
            reject(error);
          },
        });
      });
    } catch (error) {
      console.error('Error loading more comments:', error);
      this.error.set(error);
    } finally {
      this.loadingMore.set(false);
    }
  }

  /**
   * Handle reply to a specific comment
   */
  handleReply(replyData: { author: string; parentId: string }): void {
    this.replyData.set({
      author: replyData.author,
      parentId: replyData.parentId,
    });
    this.showCommentForm.set(true);

    setTimeout(() => {
      const formElement = document.getElementById('comment-form');
      if (formElement) {
        formElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }

  /**
   * Cancel reply
   */
  cancelReply(): void {
    this.replyData.set(null);
    this.showCommentForm.set(true);
  }

  /**
   * Handle comment form submission
   */
  handleCommentSubmit(commentData: any): void {
    console.log('Comment submitted:', commentData);
  }

  /**
   * Track by function for *ngFor performance
   */
  trackByCommentId(index: number, comment: Comment): string {
    return comment.id;
  }

  /**
   * Validator for content type (used for input validation)
   */
  private isValidContentType(value: string): boolean {
    return ['post', 'page'].includes(value);
  }
}
