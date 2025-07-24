import { Component, OnInit, Input, computed, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { getPosts, capitalizeWords } from '../../../utils/utils';
import { LoadingComponent } from '../../loading/loading.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { PostListingComponent } from '../../post-listing/post-listing.component';
import {
  Post,
  PageInfo,
  PostsResponse,
} from '../../../interfaces/post.interface';

const POSTS_QUERY = `
  query GetPosts(
    $first: Int = 9
    $after: String
    $category: String
    $tag: String
  ) {
    posts(
      first: $first
      after: $after
      where: { categoryName: $category, tag: $tag }
    ) {
      pageInfo {
        hasNextPage
        endCursor
      }
      edges {
        cursor
        node {
          id
          title
          date
          excerpt
          uri
          slug
          featuredImage {
            node {
              sourceUrl
              altText
            }
          }
          categories {
            nodes {
              name
              slug
            }
          }
          tags {
            nodes {
              name
              slug
            }
          }
          author {
            node {
              name
              avatar {
                url
              }
            }
          }
        }
      }
    }
  }
`;

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    EmptyStateComponent,
    PostListingComponent,
  ],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss',
})
export class HomeComponent implements OnInit {

  @Input() category: string = '';
  @Input() tag: string = '';
  @Input() titlePrefix: string = 'Blog';

  allPosts = signal<Post[]>([]);
  initialPageInfo = signal<PageInfo | null>(null);
  loading = signal<boolean>(true);
  error = signal<string | null>(null);
  loadingMore = signal<boolean>(false);

  postsPerPage: number = 9;
  private currentSlug: string = '';

  // Computed property for page title
  pageTitle = computed(() => {
    const capitalizedSlug = capitalizeWords(this.currentSlug);
    if (capitalizedSlug) {
      return `${this.titlePrefix}: ${capitalizedSlug}`;
    }
    return this.titlePrefix;
  });

  constructor() {}

  async ngOnInit(): Promise<void> {
    await this.loadPosts();
  }
  /**
   * Load posts using the getPosts utility function
   */
  private async loadPosts(after: string | null = null): Promise<void> {
    try {
      if (!after) {
        this.loading.set(true);
        this.error.set(null);
      } else {
        this.loadingMore.set(true);
      }

      // Use getPosts utility function
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
}
