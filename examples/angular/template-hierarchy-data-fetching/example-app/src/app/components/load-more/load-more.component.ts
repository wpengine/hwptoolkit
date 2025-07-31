import {
  Component,
  OnInit,
  input,
  signal,
  computed,
  effect,
  Output,
  EventEmitter,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { GraphQLService } from '../../utils/graphql.service';
import { LoadingComponent } from '../loading/loading.component';
import { EmptyStateComponent } from '../empty-state/empty-state.component';
import { POSTS_QUERY } from '../../utils/postQuery';
import { Post, PageInfo, PostsResponse } from '../../interfaces/post.interface';

@Component({
  selector: 'app-load-more',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent, EmptyStateComponent],
  templateUrl: './load-more.component.html',
  styleUrl: './load-more.component.scss',
})
export class LoadMoreComponent implements OnInit {
  initialPosts = input<Post[]>([]);
  initialPageInfo = input<PageInfo | null>(null);

  @Output() newPostsLoaded = new EventEmitter<Post[]>();
  @Output() pageInfoUpdated = new EventEmitter<PageInfo>();
  @Output() loadingStateChanged = new EventEmitter<boolean>();

  loadingMore = signal<boolean>(false);
  error = signal<any>(null);

  pageInfo = signal<PageInfo | null>(null);

  postsPerPage = 10;

  // Computed properties
  hasMorePosts = computed(() => this.pageInfo()?.hasNextPage || false);
  isLoadingMore = computed(() => this.loadingMore());

  constructor(private graphqlService: GraphQLService) {
    // Watch for changes in loading state and emit to parent
    effect(() => {
      this.loadingStateChanged.emit(this.loadingMore());
    });

    // Watch for initial page info changes
    effect(() => {
      if (this.initialPageInfo()) {
        this.pageInfo.set(this.initialPageInfo());
      }
    });
  }

  ngOnInit(): void {}

  /**
   * Load more posts (pagination)
   */
  loadMorePosts(): void {
    const currentPageInfo = this.pageInfo();
    if (!currentPageInfo?.hasNextPage || this.loadingMore()) {
      return;
    }
    this.loadingMore.set(true);
    this.error.set(null);

    this.graphqlService
      .query<PostsResponse>(POSTS_QUERY, {
        first: this.postsPerPage,
        after: currentPageInfo.endCursor,
      })
      .subscribe({
        next: (data) => {
          if (data?.posts?.edges) {
            const newPosts = data.posts.edges.map(
              (edge: { node: Post }) => edge.node
            );

            this.newPostsLoaded.emit(newPosts);

            this.pageInfo.set(data.posts.pageInfo);

            if (data.posts.pageInfo) {
              this.pageInfoUpdated.emit(data.posts.pageInfo);
            }
          }

          this.loadingMore.set(false);
        },
        error: (error) => {
          this.error.set(error);
          this.loadingMore.set(false);
        },
      });
  }
}
