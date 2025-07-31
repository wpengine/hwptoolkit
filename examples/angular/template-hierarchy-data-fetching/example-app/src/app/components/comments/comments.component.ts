import { Component, Input, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CommentFormComponent } from './comment-form/comment-form.component';
import { CommentThreadComponent } from './comment-thread/comment-thread.component';
import { GraphQLService, gql, fetchGraphQL } from '../../utils/graphql.service';
import { EmptyStateComponent } from '../empty-state/empty-state.component';
import { LoadingComponent } from '../loading/loading.component';
import {
  Comment,
  ReplyData,
  CommentPageInfo,
  CommentResponse,
} from '../../interfaces/comment.interface';

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

  private data = signal<any>(null);
  loading = signal<boolean>(true);
  error = signal<any>(null);

  allComments = signal<Comment[]>([]);
  pageInfo = signal<CommentPageInfo>({ hasNextPage: false, endCursor: null });
  loadingMore = signal<boolean>(false);

  replyData = signal<ReplyData | null>(null);
  showCommentForm = signal<boolean>(true);

  content = computed(() => this.data()?.[this.contentType] || null);

  comments = computed(() => {
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

  private async loadInitialComments(): Promise<void> {
    this.loading.set(true);
    this.error.set(null);

    fetchGraphQL<CommentResponse>(this.getCommentsQuery(this.contentType), {
      postId: this.postId,
      first: this.commentsPerPage,
      after: null,
    })
      .then((response) => {
        this.data.set(response);
        const contentData = (response as any)[this.contentType];
        if (contentData?.comments?.nodes) {
          this.allComments.set([...contentData.comments.nodes]);
          this.pageInfo.set(contentData.comments.pageInfo);
        }
      })
      .catch((error) => {
        this.error.set(error);
      })
      .finally(() => {
        this.loading.set(false);
      });
  }

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
   * Load more comments with load more button
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

      return new Promise((resolve, reject) => {
        this.graphqlService.query<any>(query, variables).subscribe({
          next: (response) => {
            const moreCommentsData = response[this.contentType];

            if (moreCommentsData?.comments?.nodes) {
              const newComments = moreCommentsData.comments.nodes;

              this.allComments.update((existing) => [
                ...existing,
                ...newComments,
              ]);

              this.pageInfo.set(moreCommentsData.comments.pageInfo);
            }

            resolve();
          },
          error: (error) => {
            this.error.set(error);
            reject(error);
          },
        });
      });
    } catch (error) {
      this.error.set(error);
    } finally {
      this.loadingMore.set(false);
    }
  }

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

  cancelReply(): void {
    this.replyData.set(null);
    this.showCommentForm.set(true);
  }

  handleCommentSubmit(commentData: any): void {
    //console.log('Comment submitted:', commentData);
  }

  /**
   * Track by function for *ngFor performance
   */
  trackByCommentId(index: number, comment: Comment): string {
    return comment.id;
  }
}
