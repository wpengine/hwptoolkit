import { Component, OnInit, Input, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router, ActivatedRoute } from '@angular/router';
import { GraphQLService, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { CommentsComponent } from '../../comments/comments.component';

interface FeaturedImage {
  node: {
    sourceUrl: string;
    altText: string;
  };
}

interface PageData {
  id: string;
  databaseId: number;
  title: string;
  content: string;
  commentCount: number;
  commentStatus: string;
  featuredImage?: FeaturedImage;
}

interface PageResponse {
  page: PageData;
}

@Component({
  selector: 'app-page',
  standalone: true,
  imports: [CommonModule, RouterModule, LoadingComponent, CommentsComponent],
  templateUrl: './page.component.html',
  styleUrl: './page.component.scss',
})
export class PageComponent implements OnInit {
  @Input() seedQuery?: any; // Data from template hierarchy if available
  @Input() slug?: string; // Optional slug override

  // Signals for reactive state
  loading = signal(true);
  error = signal<string | null>(null);
  pageData = signal<PageData | null>(null);

  // GraphQL Query
  private PAGE_QUERY = gql`
    query GetPage($slug: ID!) {
      page(id: $slug, idType: URI) {
        id
        databaseId
        title
        content
        commentCount
        commentStatus
        featuredImage {
          node {
            sourceUrl
            altText
          }
        }
      }
    }
  `;

  // Computed properties
  page = computed(() => this.pageData());

  pageId = computed(() => {
    const page = this.page();
    return page?.databaseId || null;
  });

  // Check if comments are enabled for this page
  commentsEnabled = computed(() => {
    const page = this.page();
    return (
      page?.commentStatus === 'open' ||
      (page?.commentCount && page.commentCount > 0)
    );
  });

  // Check if comments exist but are closed
  commentsClosedButExist = computed(() => {
    const page = this.page();
    return (
      page?.commentCount &&
      page.commentCount > 0 &&
      page.commentStatus !== 'open'
    );
  });

  constructor(
    private graphqlService: GraphQLService,
    private router: Router,
    private route: ActivatedRoute,
  ) {}

  ngOnInit() {
    // If we have seedQuery data from template hierarchy, use it
    if (this.seedQuery?.page) {
      console.log('📋 Using seed query data for page');
      this.pageData.set(this.seedQuery.page);
      this.loading.set(false);
    } else {
      // Otherwise, fetch the page data
      this.loadPageData();
    }
  }

  private loadPageData() {
    const uri = this.getPageSlug();

    if (!uri) {
      this.error.set('No page slug provided');
      this.loading.set(false);
      return;
    }

    console.log('🔍 Loading page data for slug:', uri);

    this.loading.set(true);
    this.error.set(null);

    this.graphqlService
      .query<PageResponse>(this.PAGE_QUERY, { slug: uri })
      .subscribe({
        next: (data) => {
          console.log('✅ Page data loaded:', data);

          if (data?.page) {
            this.pageData.set(data.page);
          } else {
            this.error.set('Page not found');
          }
          this.loading.set(false);
        },
        error: (error) => {
          console.error('❌ Error loading page:', error);
          this.error.set(error.message || 'Failed to load page');
          this.loading.set(false);
        },
      });
  }

  private getPageSlug(): string {
    // Use provided slug, or get from route
    if (this.slug) {
      return this.slug;
    }

    // Get current route path
    const currentPath = this.router.url;
    return currentPath || '/';
  }

  goHome() {
    this.router.navigate(['/']);
  }

  refreshPage() {
    this.loadPageData();
  }
}
