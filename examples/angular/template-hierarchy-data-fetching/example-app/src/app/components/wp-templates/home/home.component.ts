import { Component, OnInit, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { getPosts } from '../../../utils/utils';
import { LoadingComponent } from '../../loading/loading.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { PostListingComponent } from '../../post-listing/post-listing.component';
import {
  Post,
  PageInfo,
  PostsResponse,
} from '../../../interfaces/post.interface';
import { POSTS_QUERY } from '../../../utils/postQuery';
import { LoadMoreComponent } from '../../load-more/load-more.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    EmptyStateComponent,
    PostListingComponent,
    LoadMoreComponent,
  ],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss',
})
export class HomeComponent implements OnInit {
  allPosts = signal<Post[]>([]);
  initialPageInfo = signal<PageInfo | null>(null);
  loading = signal<boolean>(true);
  error = signal<string | null>(null);
  loadingMore = signal<boolean>(false);

  postsPerPage: number = 9;

  pageTitle = computed(() => {
    return 'Blog';
  });

  ngOnInit(): void {
    this.loadPosts();
  }

  private async loadPosts(after: string | null = null): Promise<void> {
    try {
      if (!after) {
        this.loading.set(true);
        this.error.set(null);
      } else {
        this.loadingMore.set(true);
      }

      const data = await getPosts({
        query: POSTS_QUERY,
        slug: '',
        pageSize: this.postsPerPage,
        after,
      });

      if (data?.posts) {
        this.processPosts(data);
      } else {
        this.error.set('No posts data received');
      }
    } catch (error: any) {
      this.error.set(error.message || 'Failed to load posts');
    } finally {
      this.loading.set(false);
      this.loadingMore.set(false);
    }
  }

  private processPosts(data: PostsResponse): void {
    if (data.posts?.edges) {
      const newPosts = data.posts.edges.map((edge) => edge.node);

      // If it's the first load, replace all posts
      // If it's load more, append to existing posts
      if (this.allPosts().length === 0) {
        this.allPosts.set(newPosts);
      } else {
        this.allPosts.update((currentPosts) => [...currentPosts, ...newPosts]);
      }

      this.initialPageInfo.set(data.posts.pageInfo);
    }
  }

  async loadMorePosts(): Promise<void> {
    const pageInfo = this.initialPageInfo();
    if (pageInfo?.hasNextPage && pageInfo.endCursor && !this.loadingMore()) {
      await this.loadPosts(pageInfo.endCursor);
    }
  }

  async refreshPosts(): Promise<void> {
    this.allPosts.set([]);
    this.initialPageInfo.set(null);
    await this.loadPosts();
  }

  hasMorePosts(): boolean {
    const pageInfo = this.initialPageInfo();
    return pageInfo?.hasNextPage || false;
  }

  getPostsCount(): number {
    return this.allPosts().length;
  }

  /**
   * Handles new posts emitted from the LoadMoreComponent.
   * Appends new posts to the existing list.
   * @param newPosts - Array of new Post objects
   */
  handleNewPosts(newPosts: Post[]): void {
    this.allPosts.update((currentPosts) => [...currentPosts, ...newPosts]);
  }

  handleLoading(isLoading: boolean): void {
    this.loadingMore.set(isLoading);
  }

  /**
   * Handle page info updates from blog component
   */
  handlePageInfoUpdate(pageInfo: PageInfo): void {
    this.initialPageInfo.set(pageInfo);
  }
}
